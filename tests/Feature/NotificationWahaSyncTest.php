<?php

namespace Tests\Feature;

use App\Models\HedrasoulNotification;
use App\Models\User;
use App\Models\WahaSyncProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationWahaSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_notifications_data_endpoint_returns_success_and_notifications(): void
    {
        $user = User::factory()->create();

        HedrasoulNotification::create([
            'notification_type' => 'info',
            'priority' => 'normal',
            'title' => 'Test Alert',
            'body' => 'Test notification content',
            'is_read' => false,
            'is_dismissed' => false,
        ]);

        $response = $this->actingAs($user)->getJson('/hub/notifications/data');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(1, $response->json('notifications'));
    }

    public function test_trigger_waha_sync_creates_waha_sync_process_and_notification(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/hub/waha/sync', [
            'type' => 'Contacts',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('waha_sync_processes', [
            'type' => 'sync_contacts',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('hedrasoul_notifications', [
            'notification_type' => 'info',
            'title' => 'WAHA Contacts Sync Dispatched',
        ]);
    }

    public function test_mark_notification_read_handles_numeric_and_string_ids(): void
    {
        $user = User::factory()->create();

        $notif = HedrasoulNotification::create([
            'notification_type' => 'info',
            'priority' => 'normal',
            'title' => 'Mark Read Test',
            'body' => 'Content',
            'is_read' => false,
            'is_dismissed' => false,
        ]);

        // Numeric ID test
        $response1 = $this->actingAs($user)->postJson("/hub/notifications/{$notif->id}/read");
        $response1->assertStatus(200)->assertJson(['success' => true]);
        $this->assertTrue($notif->fresh()->is_read);

        // String client-side ID test
        $response2 = $this->actingAs($user)->postJson('/hub/notifications/notif-172839210/read');
        $response2->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_waha_status_web_endpoint_returns_active_processes(): void
    {
        $user = User::factory()->create();

        WahaSyncProcess::create([
            'type' => 'sync_contacts',
            'status' => 'running',
            'progress' => 45,
        ]);

        $response = $this->actingAs($user)->getJson('/hub/waha/status');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('active_processes'));
    }
}
