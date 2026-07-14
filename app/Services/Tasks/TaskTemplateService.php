<?php

namespace App\Services\Tasks;

use App\Models\AgentTask;
use App\Models\TaskTemplate;

class TaskTemplateService
{
    /**
     * Spawn a new AgentTask from a TaskTemplate by substituting variables.
     *
     * @param  array  $variables  Key-value pairs for substitution
     * @param  array  $overrides  Optional overrides for task attributes (e.g., agent_id, priority)
     */
    public function spawnTask(TaskTemplate $template, array $variables = [], array $overrides = []): AgentTask
    {
        // 1. Validate variables
        $this->validateVariables($template, $variables);

        // 2. Substitute variables in text fields
        $title = $this->substitute($template->title_template, $variables);
        $description = $this->substitute($template->description_template ?? '', $variables);

        // 3. Substitute variables in payload JSON
        $payload = [];
        if ($template->payload_template) {
            $payloadString = json_encode($template->payload_template);
            $substitutedPayloadString = $this->substitute($payloadString, $variables);
            $payload = json_decode($substitutedPayloadString, true) ?? [];
        }

        // 4. Create the new task
        $taskData = array_merge([
            'title' => $title,
            'description' => $description,
            'type' => $template->task_type,
            'status' => AgentTask::STATUS_TODO,
            'priority' => $template->default_priority,
            'payload_data' => $payload,
            'metadata' => [
                'spawned_from_template' => $template->id,
                'template_variables' => $variables,
                'agent_type' => $template->agent_type,
            ],
        ], $overrides);

        return AgentTask::create($taskData);
    }

    /**
     * Simple string substitution for {var} syntax.
     */
    protected function substitute(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Replace {key} with value
            $text = str_replace('{'.$key.'}', (string) $value, $text);
        }

        return $text;
    }

    /**
     * Validate that all expected variables are provided.
     */
    protected function validateVariables(TaskTemplate $template, array $variables): void
    {
        $expected = $template->expected_variables ?? [];
        $missing = [];

        foreach ($expected as $var) {
            if (! array_key_exists($var, $variables)) {
                $missing[] = $var;
            }
        }

        if (! empty($missing)) {
            throw new \InvalidArgumentException('Missing required template variables: '.implode(', ', $missing));
        }
    }
}
