<?php

namespace App\Services;

use App\Models\AgentTask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReActAgentEngine
{
    /**
     * Define the tools available to the LLM.
     */
    protected array $tools = [
        [
            'name' => 'browser_action',
            'description' => 'Perform a browser action and update the dynamic plan.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'thought' => [
                        'type' => 'string',
                        'description' => 'Your internal reasoning for why you are taking this action.',
                    ],
                    'plan' => [
                        'type' => 'array',
                        'description' => 'The dynamic checklist of steps to accomplish the overall goal. Update this array every time you think. Keep completed steps, add new ones if necessary.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'step' => ['type' => 'string', 'description' => 'Description of the step'],
                                'status' => ['type' => 'string', 'enum' => ['pending', 'completed', 'failed', 'in-progress']],
                            ],
                            'required' => ['step', 'status'],
                        ],
                    ],
                    'action_name' => [
                        'type' => 'string',
                        'enum' => ['navigate', 'click', 'type', 'scroll', 'get_dom_snapshot', 'take_screenshot', 'wait', 'complete_task'],
                        'description' => 'The atomic action to execute on the browser.',
                    ],
                    'action_args' => [
                        'type' => 'object',
                        'description' => 'Arguments matching the selected tool action (e.g. url for navigate, selector for click)',
                    ],
                ],
                'required' => ['thought', 'plan', 'action_name', 'action_args'],
            ],
        ],
    ];

    public function evaluateStep(AgentTask $task, array $observation): void
    {
        $goal = $task->dynamic_system_instruction ?? $task->description ?? 'No goal provided.';

        $proofs = $task->execution_proof ?? [];
        if (! is_array($proofs)) {
            $proofs = [];
        }

        $currentPlan = $task->plan ?? [];
        if (! is_array($currentPlan)) {
            $currentPlan = [];
        }

        // Fix 2: Programmatically extract URL and force navigate on first step
        if (empty($proofs)) {
            preg_match('~https?://[^\s"]+~', $goal, $matches);
            if (! empty($matches[0])) {
                $url = rtrim($matches[0], ')'); // Clean up any trailing parentheses
                $reactAction = ['command' => 'navigate', 'url' => $url];
                $proofs[] = [
                    'step_number' => 1,
                    'thought' => "Navigating to $url",
                    'action_sent' => $reactAction,
                ];

                $payload = $task->payload_data;
                if (is_string($payload)) {
                    $payload = json_decode($payload, true) ?? [];
                }
                if (! is_array($payload)) {
                    $payload = [];
                }
                $payload['react_action'] = $reactAction;
                $task->update([
                    'status' => 'todo',
                    'execution_proof' => $proofs,
                    'plan' => $currentPlan,
                    'payload_data' => $payload,
                ]);

                return; // Skip LLM call
            }
        }

        // Save the incoming observation if it's not the initial state
        if (! empty($observation) && count($proofs) > 0) {
            $lastStepIndex = count($proofs) - 1;
            if (! isset($proofs[$lastStepIndex]['observation_received'])) {
                $proofs[$lastStepIndex]['observation_received'] = $observation;
            } else {
                // If it somehow already has an observation, push a new dummy step or override.
                // We'll override for robustness.
                $proofs[$lastStepIndex]['observation_received'] = $observation;
            }
        }

        // Limit loop safety check
        if (count($proofs) >= 15) {
            $task->update(['status' => 'failed', 'execution_proof' => $proofs]);
            Log::warning("[ReAct] Task {$task->id} reached max 15 steps. Failing task.");

            return;
        }

        $systemPrompt = "You are an autonomous Browser Agent Executor.
Your overarching goal is: \"{$goal}\".
You have a Dynamic Plan checklist. You MUST update and return the full 'plan' array with every tool call.
Mark completed steps as 'completed', the current step as 'in-progress', and future steps as 'pending'.
IF YOU HAVE NOT NAVIGATED TO THE TARGET WEBSITE YET, YOUR VERY FIRST ACTION MUST BE A 'navigate' ACTION WITH THE TARGET URL FROM THE GOAL INSTRUCTION.
If the goal is fully achieved, call the 'complete_task' tool with your final findings.

Current Plan Status:
".json_encode($currentPlan, JSON_UNESCAPED_UNICODE).'
';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add history of steps
        foreach ($proofs as $step) {
            if (isset($step['thought'])) {
                $messages[] = ['role' => 'assistant', 'content' => "Thought: {$step['thought']}"];
            }
            if (isset($step['action_sent'])) {
                $messages[] = ['role' => 'assistant', 'content' => 'Tool Call: '.json_encode($step['action_sent'])];
            }
            if (isset($step['observation_received'])) {
                // Extract meaningful parts of observation to avoid context explosion
                $obsResult = $step['observation_received']['action_result'] ?? $step['observation_received'];
                $messages[] = ['role' => 'user', 'content' => 'Observation: '.json_encode($obsResult)];
            }
        }

        if (empty($proofs)) {
            $messages[] = ['role' => 'user', 'content' => 'Start execution. There is no observation yet, so you should probably navigate to the relevant URL or get a DOM snapshot if you are already there.'];
        }

        try {
            // Call LLM via LiteLLM / Gateway
            // Using Gemini 1.5 Pro via local gateway as per spec
            $response = Http::timeout(60)->withHeaders(['Authorization' => 'Bearer nexus-souly-litellm-2026'])->post('http://172.17.0.1:8889/v1/chat/completions', [
                'model' => 'gemini-3.5-flash',
                'messages' => $messages,
                'tools' => [
                    [
                        'type' => 'function',
                        'function' => $this->tools[0],
                    ],
                ],
                'tool_choice' => [
                    'type' => 'function',
                    'function' => ['name' => 'browser_action'],
                ],
            ]);

            $result = $response->json();

            if (! isset($result['choices'][0]['message']['tool_calls'][0]['function']['arguments'])) {
                throw new \Exception('LLM did not return a valid tool call.');
            }

            $argsJson = $result['choices'][0]['message']['tool_calls'][0]['function']['arguments'];
            $args = json_decode($argsJson, true);

            if (! $args || ! isset($args['action_name'])) {
                throw new \Exception("Invalid JSON arguments from LLM: $argsJson");
            }

            $thought = $args['thought'] ?? 'No thought provided.';
            $newPlan = $args['plan'] ?? $currentPlan;
            $command = $args['action_name'];
            $commandArgs = $args['action_args'] ?? [];

            // If LLM says complete_task, we end the loop.
            if ($command === 'complete_task') {
                $proofs[] = [
                    'step_number' => count($proofs) + 1,
                    'thought' => $thought,
                    'action_sent' => ['command' => 'complete_task'],
                    'observation_received' => $commandArgs,
                ];
                $task->update([
                    'status' => 'completed',
                    'execution_proof' => $proofs,
                    'plan' => $newPlan,
                    'result_data' => $commandArgs,
                ]);

                return;
            }

            // Build ReAct action for Extension
            $reactAction = array_merge(['command' => $command], $commandArgs);

            // Record step
            $proofs[] = [
                'step_number' => count($proofs) + 1,
                'thought' => $thought,
                'action_sent' => $reactAction,
            ];

            // Update task and push back to "todo" so Browser picks it up immediately
            $payload = $task->payload_data;
            if (is_string($payload)) {
                $payload = json_decode($payload, true) ?? [];
            }
            if (! is_array($payload)) {
                $payload = [];
            }
            $payload['react_action'] = $reactAction;
            $task->update([
                'status' => 'todo',
                'execution_proof' => $proofs,
                'plan' => $currentPlan,
                'payload_data' => $payload,
            ]);

        } catch (\Exception $e) {
            Log::error('[ReAct] Evaluation failed: '.$e->getMessage());
            $task->update([
                'status' => 'failed',
                'result_data' => ['error' => $e->getMessage()],
            ]);
        }
    }
}
