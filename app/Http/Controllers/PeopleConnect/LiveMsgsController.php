<?php

namespace App\Http\Controllers\PeopleConnect;

use App\Http\Controllers\Controller;
use App\Http\Requests\TriggerSyncRequest;
use App\Jobs\PeopleConnect\SyncWahaContactsJob;
use App\Jobs\PeopleConnect\SyncWahaConversationsJob;
use App\Models\PeopleConnect\PeopleConnectMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveMsgsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->input('per_page', 50), 200));

        $messages = PeopleConnectMessage::with('conversation.contact')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($messages);
    }

    public function triggerSync(TriggerSyncRequest $request): JsonResponse
    {
        $type = $request->validated()['type'] ?? $request->input('type', 'all');

        if ($type === 'contacts' || $type === 'all') {
            SyncWahaContactsJob::dispatch();
        }

        if ($type === 'conversations' || $type === 'all') {
            SyncWahaConversationsJob::dispatch();
        }

        return response()->json([
            'message' => 'Sync triggered successfully',
            'type' => $type,
        ]);
    }
}
