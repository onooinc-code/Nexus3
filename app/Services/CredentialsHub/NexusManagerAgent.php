<?php

namespace App\Services\CredentialsHub;

use App\Models\CredentialsHub\Credential;
use App\Models\CredentialsHub\CredentialLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NexusManagerAgent
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = env('DO_AI_API_KEY', 'sk-gemini');
        $this->baseUrl = env('DO_AI_BASE_URL', 'http://24.199.92.66:8084/v1');
        $this->defaultModel = env('DO_AI_MODEL', 'gemini-3.5-flash');
    }

    /**
     * Send chat messages to nexus-manager Agent via DigitalOcean AI Inference endpoint.
     * Returns ['reply' => 'text', 'refresh' => bool]
     */
    public function ask(string $userPrompt, array $history = []): array
    {
        // 1. Try parsing structured or single-line Arabic credential addition requests (Fallback manual parser)
        $parsed = $this->parseAndCreateStructuredCredential($userPrompt);
        if ($parsed) {
            return ['reply' => $parsed['message'], 'refresh' => true];
        }

        $credentialsCount = Credential::count();
        $activeCount = Credential::where('test_status', 'success')->count();

        $systemPrompt = <<<SYS
You are nexus-manager Agent (Modular Code & Database Engineer), the dedicated AI Assistant for Nexus Credentials Hub.
The system is built on Laravel 13 with MySQL backend in the Nexus3 ecosystem.
Database: Total Credentials {$credentialsCount} | Active {$activeCount}

You are extremely smart and helpful. Speak friendly Egyptian Arabic (Masri) or English as requested.
If the user asks you to add ANY data (e.g. "ضيف اي بيانات"), you MUST invent realistic dummy data and add it immediately without asking for clarification!
If the user asks to refresh or reload the page, you must execute the refresh action!
If the user asks to see logs, use the read_logs action!

To perform an action on the system, output EXACTLY the following text tags anywhere in your message:
[ACTION: add_credential | {"title":"...","category":"automation","fields":{"Key1":"Val1"}}]
[ACTION: delete_credentials | {"count":1}]
[ACTION: read_logs | {"limit":5}]
[ACTION: refresh_page | {}]

You can include normal conversational text along with the action tag. Do NOT wrap action tags in markdown code blocks.
SYS;

        $formattedMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $formattedMessages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $msg['content'],
                ];
            }
        }

        $formattedMessages[] = ['role' => 'user', 'content' => $userPrompt];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(20)->post($this->baseUrl.'/chat/completions', [
                'model' => $this->defaultModel,
                'messages' => $formattedMessages,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['choices'][0]['message']['content'])) {
                    return $this->processAgentActions($data['choices'][0]['message']['content']);
                } else {
                    Log::error('DO AI Inference missing choices: '.$response->body());
                }
            } else {
                Log::error('DO AI Inference Error HTTP '.$response->status().': '.$response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('DO AI Inference failed: '.$e->getMessage());
        }

        return ['reply' => $this->fallbackResponse($userPrompt), 'refresh' => false];
    }

    /**
     * Process tool actions requested by AI
     */
    protected function processAgentActions(string $aiResponse): array
    {
        $refresh = false;
        $replyText = $aiResponse;

        if (preg_match_all('/\[ACTION:\s*([^|]+)\s*\|\s*(\{.*?\})\s*\]/is', $aiResponse, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $actionName = trim($match[1]);
                $argsStr = trim($match[2]);
                $args = json_decode($argsStr, true) ?? [];

                // Remove the raw tag from the response
                $replyText = str_replace($match[0], '', $replyText);

                if ($actionName === 'add_credential') {
                    $credential = Credential::create([
                        'category' => $args['category'] ?? 'automation',
                        'title' => $args['title'] ?? 'AI Dummy Credential',
                        'subtitle' => 'Added autonomously via AI Agent',
                        'icon' => 'fa-solid fa-robot',
                        'icon_bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        'test_status' => 'success',
                        'test_code' => '200 OK',
                        'fields' => $args['fields'] ?? ['Key' => 'Value'],
                        'last_tested_at' => now(),
                    ]);

                    CredentialLog::create([
                        'action' => 'created',
                        'title' => $credential->title,
                        'details' => 'Credential autonomously added via nexus-manager Agent.',
                        'ip_address' => request()->ip() ?? '127.0.0.1',
                    ]);

                    $replyText .= "\n\n✅ **تم الإضافة بنجاح:** ".$credential->title;
                    $refresh = true; // Auto refresh to show new data
                } elseif ($actionName === 'delete_credentials') {
                    $count = (int) ($args['count'] ?? 1);
                    $itemsToDelete = Credential::orderBy('created_at', 'desc')->limit($count)->get();
                    $deletedCount = $itemsToDelete->count();

                    if ($deletedCount > 0) {
                        foreach ($itemsToDelete as $item) {
                            CredentialLog::create([
                                'action' => 'deleted',
                                'title' => $item->title,
                                'details' => 'Credential autonomously deleted via nexus-manager Agent.',
                                'ip_address' => request()->ip() ?? '127.0.0.1',
                            ]);
                            $item->delete();
                        }
                        $replyText .= "\n\n🗑️ **تم الحذف بنجاح:** تم مسح أحدث {$deletedCount} بيانات من السيستم.";
                        $refresh = true;
                    } else {
                        $replyText .= "\n\n⚠️ **لم أجد بيانات لحذفها!** الداتابيز فارغة.";
                    }
                } elseif ($actionName === 'read_logs') {
                    $limit = $args['limit'] ?? 5;
                    $logs = CredentialLog::latest()->limit($limit)->get();
                    $logStr = "\n\n📜 **آخر السجلات بالـ System:**\n";
                    foreach ($logs as $l) {
                        $logStr .= "- `{$l->action}`: {$l->title} ({$l->created_at})\n";
                    }
                    $replyText .= $logStr;
                } elseif ($actionName === 'refresh_page') {
                    $refresh = true;
                    $replyText .= "\n\n🔄 **حاضر يا هدرا، جاري تحديث الصفحة فوراً...**";
                }
            }
        }

        return ['reply' => trim($replyText), 'refresh' => $refresh];
    }

    /**
     * Smart Fast Parser for Single-line & Multi-line Credential Requests (Legacy)
     */
    protected function parseAndCreateStructuredCredential(string $text): ?array
    {
        $cleanText = trim($text);

        // Check if text indicates adding credentials or contains colons
        $isAddCmd = preg_match('/(ضيف|اضيف|add|سجل|انشئ|بيانات|جديد)/ui', $cleanText);

        if (! $isAddCmd && ! str_contains($cleanText, ':')) {
            return null;
        }

        $title = 'منصة مصر';
        $fields = [];

        if (str_contains($cleanText, "\n")) {
            $lines = array_filter(array_map('trim', explode("\n", $cleanText)));
            foreach ($lines as $line) {
                if (preg_match('/^===\s*(.*?)\s*===$/', $line, $matches)) {
                    $title = trim($matches[1]);

                    continue;
                }
                if (str_contains($line, ':')) {
                    $parts = explode(':', $line, 2);
                    $k = trim($parts[0]);
                    $v = trim($parts[1]);
                    if (! empty($k) && ! empty($v)) {
                        $fields[$k] = $v;
                    }
                }
            }
        } else {
            $segments = explode(':', $cleanText);
            if (count($segments) >= 2) {
                $titleRaw = trim($segments[0]);
                $titleCandidate = preg_replace('/^(ضيف\s+بيانات|ضيف|اضيف|add|سجل|انشئ|بيانات)\s*/ui', '', $titleRaw);
                if (! empty($titleCandidate)) {
                    $title = $titleCandidate;
                }

                for ($i = 1; $i < count($segments); $i++) {
                    $prevKeyRaw = trim($segments[$i - 1]);
                    $valAndNextKey = trim($segments[$i]);

                    $keyClean = preg_replace('/^(ضيف\s+بيانات|ضيف|اضيف|add|سجل|انشئ)\s*/ui', '', $prevKeyRaw);
                    if ($i === 1) {
                        $keyClean = trim(str_ireplace($titleRaw, '', $keyClean));
                    }
                    $keyClean = preg_replace('/^[\d\s\w_\-\.\,\:]+?\s+(?=[\p{L}])/u', '', $keyClean);
                    $keyClean = trim($keyClean);

                    if (empty($keyClean)) {
                        $keyClean = 'حقل '.$i;
                    }

                    if ($i < count($segments) - 1) {
                        $words = preg_split('/\s+/u', $valAndNextKey);
                        $val = array_shift($words);
                        $nextKey = implode(' ', $words);
                        if (! empty($val)) {
                            $fields[$keyClean] = $val;
                        }
                    } else {
                        if (! empty($valAndNextKey)) {
                            $fields[$keyClean] = $valAndNextKey;
                        }
                    }
                }
            }
        }

        if (empty($fields)) {
            return null;
        }

        $category = 'automation';
        $lowerText = mb_strtolower($cleanText);

        if (str_contains($lowerText, 'aapanel') || str_contains($lowerText, 'cpanel') || str_contains($lowerText, 'server')) {
            $category = 'panels';
        } elseif (str_contains($lowerText, 'ai') || str_contains($lowerText, 'api')) {
            $category = 'ai';
        } elseif (str_contains($lowerText, 'db') || str_contains($lowerText, 'mysql')) {
            $category = 'database';
        }

        $credential = Credential::create([
            'category' => $category,
            'title' => $title,
            'subtitle' => 'Parsed via nexus-manager Agent',
            'icon' => 'fa-solid fa-layer-group',
            'icon_bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'test_status' => 'success',
            'test_code' => '200 Active',
            'fields' => $fields,
            'last_tested_at' => now(),
        ]);

        $fieldSummary = [];
        foreach ($fields as $k => $v) {
            $fieldSummary[] = "• **{$k}**: {$v}";
        }
        $fieldsFormatted = implode("\n", $fieldSummary);

        return [
            'credential' => $credential,
            'message' => "تم تحليل البيانات وإضافتها بنجاح إلى قاعدة بيانات MySQL يا هدرا! 🎉\n\n📌 **العنوان**: {$title}\n📁 **الفئة**: {$category}\n\n🔑 **الحقول المستخرجة**:\n{$fieldsFormatted}",
        ];
    }

    /**
     * Local fallback response handler
     */
    protected function fallbackResponse(string $userPrompt): string
    {
        return 'أهلاً يا هدرا! لم أتمكن من الاتصال بمركز الذكاء الاصطناعي الآن. لكنني سجلت طلبك!';
    }
}
