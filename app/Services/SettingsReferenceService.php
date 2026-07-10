<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SettingsReferenceService
{
    /**
     * Get API endpoints documentation from JSON file.
     */
    public function getApiEndpointsDocumentation(): array
    {
        return $this->readJsonFile('apis.json');
    }

    /**
     * Get Services documentation from JSON file.
     */
    public function getServicesDocumentation(): array
    {
        return $this->readJsonFile('services.json');
    }

    /**
     * Get Jobs documentation from JSON file.
     */
    public function getJobsDocumentation(): array
    {
        return $this->readJsonFile('jobs.json');
    }

    /**
     * Get system metrics for settings.
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
     * Get live dashboard statistics for the settings table.
     *
     * Schema confirmed:
     * - is_public  (boolean) → public vs private
     * - is_encrypted (boolean) → encrypted count
     * - group      (string)  → grouping
     * - updated_at (timestamp, standard Laravel) → recent changes
     *
     * @return array{
     *     total_settings: int,
     *     encrypted_count: int,
     *     public_count: int,
     *     private_count: int,
     *     groups: array<int, array{group: string, count: int}>,
     *     recent_changes: array<int, array{date: string, count: int}>,
     * }
     */
    public function getStatistics(): array
    {
        try {
            $totalSettings = Setting::count();
            $encryptedCount = Setting::where('is_encrypted', true)->count();
            $publicCount = Setting::where('is_public', true)->count();
            $privateCount = Setting::where('is_public', false)->count();

            /** @var array<int, array{group: string, count: int}> $groups */
            $groups = Setting::select('group', DB::raw('COUNT(*) as count'))
                ->groupBy('group')
                ->orderByDesc('count')
                ->get()
                ->map(fn ($row) => [
                    'group' => $row->group ?? 'غير مصنف',
                    'count' => (int) $row->count,
                ])
                ->toArray();

            // Only query recent_changes if updated_at column exists
            $recentChanges = [];
            if (Schema::hasColumn('settings', 'updated_at')) {
                $recentChanges = Setting::select(
                    DB::raw('DATE(updated_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                    ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
                    ->groupBy(DB::raw('DATE(updated_at)'))
                    ->orderBy(DB::raw('DATE(updated_at)'))
                    ->get()
                    ->map(fn ($row) => [
                        'date' => $row->date,
                        'count' => (int) $row->count,
                    ])
                    ->toArray();
            }

            return [
                'total_settings' => $totalSettings,
                'encrypted_count' => $encryptedCount,
                'public_count' => $publicCount,
                'private_count' => $privateCount,
                'groups' => $groups,
                'recent_changes' => $recentChanges,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'total_settings' => 0,
                'encrypted_count' => 0,
                'public_count' => 0,
                'private_count' => 0,
                'groups' => [],
                'recent_changes' => [],
            ];
        }
    }

    /**
     * Read and decode JSON file from public data directory.
     */
    private function readJsonFile(string $filename): array
    {
        $path = public_path("data/settings-reference/{$filename}");

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        return json_decode($content, true) ?? [];
    }
}
