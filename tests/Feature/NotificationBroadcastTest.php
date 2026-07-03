<?php

namespace Tests\Feature;

use App\Events\NotificationBroadcasted;
use App\Models\User;
use App\Models\UserPushToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_fcm_token()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/notifications/fcm-token', [
                'token' => 'fcm_test_token_123',
                'device_name' => 'Chrome Web',
                'platform' => 'web',
            ])
            ->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.token', 'fcm_test_token_123');

        $this->assertDatabaseHas('user_push_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm_test_token_123',
        ]);
    }

    public function test_sends_fcm_notification_when_driver_is_fcm()
    {
        config([
            'notifications.driver' => 'fcm',
            'notifications.fcm.server_key' => 'test_server_key',
        ]);

        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response(['success' => 1, 'failure' => 0], 200),
        ]);

        $user = User::factory()->create();
        UserPushToken::create([
            'user_id' => $user->id,
            'token' => 'fcm_test_token_456',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/notifications/broadcast', [
                'user_id' => $user->id,
                'title' => 'Hello',
                'body' => 'World',
                'type' => 'info',
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://fcm.googleapis.com/fcm/send'
                && in_array('fcm_test_token_456', $request->data()['registration_ids']);
        });
    }

    public function test_dispatches_reverb_event_when_driver_is_reverb()
    {
        config(['notifications.driver' => 'reverb']);

        Event::fake([NotificationBroadcasted::class]);

        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/notifications/broadcast', [
                'user_id' => $user->id,
                'title' => 'Hello',
                'body' => 'World',
                'type' => 'info',
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        Event::assertDispatched(NotificationBroadcasted::class);
    }
}
