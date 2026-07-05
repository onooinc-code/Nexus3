<?php

namespace App\Http\Controllers;

use App\Events\NotificationBroadcasted;
use App\Models\UserPushToken;
use App\Services\FirebasePushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationBroadcastController extends Controller
{
    /**
     * Send a broadcast notification to a user.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|in:info,warning,success,error',
            'icon' => 'nullable|string|url',
            'badge' => 'nullable|string|url',
            'actions' => 'nullable|array',
            'data' => 'nullable|array',
            'requireInteraction' => 'boolean',
        ]);

        $notificationData = [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'icon' => $validated['icon'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'actions' => $validated['actions'] ?? [],
            'data' => $validated['data'] ?? [],
            'requireInteraction' => $validated['requireInteraction'] ?? false,
        ];

        $payloadData = $notificationData['data'];

        try {
            $driver = app(\App\Services\SettingCacheService::class)->get('notifications.driver', config('notifications.driver', 'reverb'));

            if ($driver === 'fcm') {
                $success = app(FirebasePushService::class)->sendToUsers([
                    $validated['user_id'],
                ], $notificationData, [
                    'click_action' => $payloadData['click_action'] ?? url('/'),
                ]);

                return response()->json([
                    'success' => $success,
                    'message' => $success ? 'FCM notification sent successfully' : 'No FCM tokens available',
                    'notification' => [
                        'title' => $validated['title'],
                        'body' => $validated['body'],
                        'type' => $validated['type'],
                    ],
                ], $success ? 200 : 202);
            }

            NotificationBroadcasted::dispatch(
                userId: $validated['user_id'],
                notification: $notificationData,
                type: $validated['type']
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification broadcast successfully',
                'notification' => [
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'type' => $validated['type'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send broadcast notification to multiple users.
     */
    public function sendBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|in:info,warning,success,error',
            'icon' => 'nullable|string|url',
            'badge' => 'nullable|string|url',
            'actions' => 'nullable|array',
            'data' => 'nullable|array',
            'requireInteraction' => 'boolean',
        ]);

        $notificationData = [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'icon' => $validated['icon'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'actions' => $validated['actions'] ?? [],
            'data' => $validated['data'] ?? [],
            'requireInteraction' => $validated['requireInteraction'] ?? false,
        ];

        try {
            $driver = app(\App\Services\SettingCacheService::class)->get('notifications.driver', config('notifications.driver', 'reverb'));

            if ($driver === 'fcm') {
                $success = app(FirebasePushService::class)->sendToUsers($validated['user_ids'], $notificationData, [
                    'click_action' => $validated['data']['click_action'] ?? url('/'),
                ]);

                return response()->json([
                    'success' => $success,
                    'message' => $success ? 'FCM batch notification sent successfully' : 'No FCM tokens available',
                    'total' => count($validated['user_ids']),
                ], $success ? 200 : 202);
            }

            $sent = 0;
            $failed = [];

            foreach ($validated['user_ids'] as $userId) {
                try {
                    NotificationBroadcasted::dispatch(
                        userId: $userId,
                        notification: $notificationData,
                        type: $validated['type']
                    );
                    $sent++;
                } catch (\Exception $e) {
                    $failed[] = [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => count($failed) === 0,
                'message' => "Notification sent to {$sent} user(s)",
                'sent' => $sent,
                'failed' => $failed,
                'total' => count($validated['user_ids']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send batch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register or refresh a user's FCM token.
     */
    public function registerFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        $token = UserPushToken::updateOrCreate([
            'user_id' => $user->id,
            'token' => $validated['token'],
        ], [
            'device_name' => $validated['device_name'] ?? $request->userAgent(),
            'platform' => $validated['platform'] ?? 'web',
            'last_used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $token,
        ], 201);
    }

    /**
     * Return public Firebase config for the service worker.
     */
    public function fcmConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'apiKey' => config('notifications.fcm.api_key'),
                'authDomain' => config('notifications.fcm.auth_domain'),
                'projectId' => config('notifications.fcm.project_id'),
                'storageBucket' => config('notifications.fcm.storage_bucket'),
                'messagingSenderId' => config('notifications.fcm.messaging_sender_id'),
                'appId' => config('notifications.fcm.app_id'),
                'measurementId' => config('notifications.fcm.measurement_id'),
                'vapidKey' => config('notifications.fcm.vapid_key'),
                'serviceWorkerUrl' => config('notifications.fcm.service_worker_url'),
            ],
        ]);
    }
}
