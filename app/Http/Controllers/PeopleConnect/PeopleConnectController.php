<?php

namespace App\Http\Controllers\PeopleConnect;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReplyModeRequest;
use App\Services\PeopleConnect\PeopleConnectConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleConnectController extends Controller
{
    public function __construct(
        protected PeopleConnectConversationService $conversationService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        return response()->json($this->conversationService->getSystemStats());
    }

    public function search(Request $request): JsonResponse
    {
        $results = $this->conversationService->searchConversations($request->input('q'));

        return response()->json($results);
    }

    public function showConversation(int|string $id): JsonResponse
    {
        $conversation = $this->conversationService->getConversationDetails($id);

        return response()->json($conversation);
    }

    public function updateReplyMode(UpdateReplyModeRequest $request, int|string $id): JsonResponse
    {
        $replyMode = $request->validated()['reply_mode'] ?? $request->input('reply_mode');
        $conversation = $this->conversationService->updateReplyMode($id, $replyMode);

        return response()->json([
            'message' => 'Reply mode updated successfully',
            'conversation' => $conversation,
        ]);
    }
}
