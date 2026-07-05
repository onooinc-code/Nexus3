<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;

class SettingsReferenceService
{
    /**
     * Get API endpoints documentation from JSON file.
     *
     * @return array
     */
    public function getApiEndpointsDocumentation(): array
    {
        return $this->readJsonFile('apis.json');
    }

    /**
     * Get Services documentation from JSON file.
     *
     * @return array
     */
    public function getServicesDocumentation(): array
    {
        return $this->readJsonFile('services.json');
    }

    /**
     * Get Jobs documentation from JSON file.
     *
     * @return array
     */
    public function getJobsDocumentation(): array
    {
        return $this->readJsonFile('jobs.json');
    }

    /**
     * Get system metrics for settings.
     *
     * @return array
     */
    public function getSystemMetrics(): array
    {
        return [
            'total_settings' => Setting::count(),
            'public_settings' => Setting::public()->count(),
            'private_settings' => Setting::private()->count(),
            'global_settings' => Setting::byScope('global')->count(),
        ];
    }

    /**
     * Read and decode JSON file from public data directory.
     *
     * @param string $filename
     * @return array
     */
    private function readJsonFile(string $filename): array
    {
        $path = public_path("data/settings-reference/{$filename}");

        if (!File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        
        return json_decode($content, true) ?? [];
    }
}
