<?php

namespace Tests\Feature\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TelemetryUploadTest extends TestCase
{
    /**
     * Test the telemetry upload route works.
     */
    public function test_telemetry_upload_works(): void
    {
        Storage::fake('public');

        $deviceId = 'test-device-123';
        $file = UploadedFile::fake()->image('screenshot.png');

        $response = $this->postJson("/api/telemetry/upload/{$deviceId}", [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['url']);

        // Assert the file was stored
        $files = Storage::disk('public')->files("screenshots/{$deviceId}");
        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.webp', $files[0]);
    }

    public function test_telemetry_upload_fails_without_image(): void
    {
        $deviceId = 'test-device-123';

        $response = $this->postJson("/api/telemetry/upload/{$deviceId}");

        $response->assertStatus(400);
        $response->assertJson(['error' => 'No image found']);
    }
}
