<?php

namespace App\Http\Controllers\CredentialsHub;

use App\Http\Controllers\Controller;
use App\Jobs\CredentialsHub\CheckCredentialHealthJob;
use App\Models\CredentialsHub\Credential;
use App\Models\CredentialsHub\CredentialChat;
use App\Models\CredentialsHub\CredentialLog;
use App\Services\CredentialsHub\NexusManagerAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CredentialsHubController extends Controller
{
    /**
     * Display main CredentialsHub page.
     */
    public function index(Request $request): View
    {
        $categories = [
            ['id' => 'all', 'name' => 'All Resources', 'icon' => 'fa-solid fa-layer-group'],
            ['id' => 'ai', 'name' => 'AI & LLM Providers', 'icon' => 'fa-solid fa-brain'],
            ['id' => 'panels', 'name' => 'Control Panels & Servers', 'icon' => 'fa-solid fa-server'],
            ['id' => 'database', 'name' => 'DB & Storage Profiles', 'icon' => 'fa-solid fa-database'],
            ['id' => 'google', 'name' => 'Google Auth Cookies', 'icon' => 'fa-brands fa-google'],
            ['id' => 'automation', 'name' => 'Automation & APIs', 'icon' => 'fa-solid fa-bolt'],
        ];

        $credentials = Credential::orderBy('created_at', 'desc')->get();

        $testedActiveCount = $credentials->where('test_status', 'success')->count();
        $aiProvidersCount = $credentials->where('category', 'ai')->count();

        // Retrieve Chat History & Activity Logs
        $chatHistory = CredentialChat::orderBy('created_at', 'asc')->take(50)->get();
        $activityLogs = CredentialLog::orderBy('created_at', 'desc')->take(30)->get();

        return view('CredentialsHub.index', compact(
            'categories',
            'credentials',
            'testedActiveCount',
            'aiProvidersCount',
            'chatHistory',
            'activityLogs'
        ));
    }

    /**
     * Store new credential in MySQL.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'raw_fields' => 'nullable|string',
        ]);

        $fieldsObj = [];

        if (! empty($validated['raw_fields'])) {
            $lines = explode("\n", $validated['raw_fields']);
            foreach ($lines as $line) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $fieldsObj[trim($parts[0])] = trim($parts[1]);
                }
            }
        }

        if (empty($fieldsObj)) {
            $fieldsObj = ['Value' => $validated['raw_fields'] ?? 'Default Item'];
        }

        $credential = Credential::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'subtitle' => $validated['subtitle'] ?? 'User Added Resource',
            'icon' => 'fa-solid fa-key',
            'icon_bg' => 'bg-green-500/10 text-green-400 border-green-500/20',
            'test_status' => 'success',
            'test_code' => 'Custom',
            'fields' => $fieldsObj,
            'last_tested_at' => now(),
        ]);

        CredentialLog::create([
            'action' => 'created',
            'title' => $credential->title,
            'details' => "Category: {$credential->category}, Fields: ".json_encode(array_keys($fieldsObj)),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Credential created successfully in MySQL',
            'data' => $credential,
        ]);
    }

    /**
     * Inline update single field key-value.
     */
    public function updateField(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $credential = Credential::findOrFail($id);
        $fields = $credential->fields ?? [];
        $oldVal = $fields[$validated['key']] ?? '';
        $fields[$validated['key']] = $validated['value'];

        $credential->update(['fields' => $fields]);

        CredentialLog::create([
            'action' => 'updated',
            'title' => $credential->title,
            'details' => "Updated field '{$validated['key']}' from '{$oldVal}' to '{$validated['value']}'",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Updated field {$validated['key']} in MySQL",
            'data' => $credential,
        ]);
    }

    /**
     * Delete credential from MySQL.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $credential = Credential::findOrFail($id);
        $title = $credential->title;
        $credential->delete();

        CredentialLog::create([
            'action' => 'deleted',
            'title' => $title,
            'details' => "Credential ID {$id} deleted from MySQL",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Credential deleted from MySQL',
        ]);
    }

    /**
     * Run test for single credential.
     */
    public function testSingle(Request $request, int $id): JsonResponse
    {
        $credential = Credential::findOrFail($id);
        CheckCredentialHealthJob::dispatchSync($credential);

        CredentialLog::create([
            'action' => 'tested',
            'title' => $credential->title,
            'details' => "Ping test code: {$credential->test_code}, status: {$credential->test_status}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Ping test completed for {$credential->title}",
            'data' => $credential->fresh(),
        ]);
    }

    /**
     * Run test for all credentials.
     */
    public function testAll(Request $request): JsonResponse
    {
        CheckCredentialHealthJob::dispatchSync();

        CredentialLog::create([
            'action' => 'tested',
            'title' => 'All System Credentials',
            'details' => 'Dispatched bulk health test for all items',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tested all credentials successfully in MySQL',
            'data' => Credential::all(),
        ]);
    }

    /**
     * Chat with nexus-manager Agent.
     */
    public function agentChat(Request $request, NexusManagerAgent $agent): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $prompt = $validated['prompt'];

        // Save User Message to Database
        CredentialChat::create([
            'role' => 'user',
            'content' => $prompt,
        ]);

        $response = $agent->ask($prompt, $validated['history'] ?? []);

        // Save Assistant Message to Database
        CredentialChat::create([
            'role' => 'assistant',
            'content' => $response['reply'],
        ]);

        return response()->json([
            'status' => 'success',
            'reply' => $response['reply'],
            'refresh' => $response['refresh'] ?? false,
            'credentials' => Credential::orderBy('created_at', 'desc')->get(),
        ]);
    }
}
