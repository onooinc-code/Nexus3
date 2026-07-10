<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Setting;
use App\Services\CredentialValidationService;
use App\Services\LogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SettingsHubAdminController
 *
 * Provides admin dashboard endpoints for monitoring, auditing, and managing
 * the SettingsHub including health status, audit trails, and compliance checks.
 */
class SettingsHubAdminController extends Controller
{
    public function __construct(
        protected CredentialValidationService $validationService,
        protected LogService $logService,
    ) {}

    /**
     * Get comprehensive admin dashboard overview.
     */
    public function dashboardOverview(): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $stats = [
            'total_settings' => Setting::count(),
            'total_encrypted' => Setting::where('is_encrypted', true)->count(),
            'total_public' => Setting::where('is_public', true)->count(),
            'total_private' => Setting::where('is_public', false)->count(),
            'by_group' => Setting::select('group')->selectRaw('count(*) as count')
                ->groupBy('group')
                ->get()
                ->mapWithKeys(fn ($g) => [$g->group => $g->count])
                ->all(),
            'by_scope' => Setting::select('scope')->selectRaw('count(*) as count')
                ->groupBy('scope')
                ->get()
                ->mapWithKeys(fn ($s) => [$s->scope => $s->count])
                ->all(),
        ];

        $health = [
            'credential_validation' => $this->validationService->validateAllCredentials(),
            'last_health_check' => now()->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'health' => $health,
            ],
        ]);
    }

    /**
     * Get audit trail for settings changes.
     */
    public function auditTrail(Request $request): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $limit = (int) $request->input('limit', 100);
        $type = $request->input('type'); // 'setting', 'security', 'database', etc.

        $query = Log::query();

        if ($type) {
            $query->where('type', $type);
        }

        $logs = $query
            ->where(function ($q) {
                $q->where('channel', 'system')
                    ->orWhere('channel', 'monitoring');
            })
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs->map(fn ($log) => [
                'id' => $log->id,
                'timestamp' => $log->created_at->toIso8601String(),
                'level' => $log->level,
                'type' => $log->type,
                'message' => $log->message,
                'user_id' => $log->user_id,
                'context' => $log->context,
            ])->all(),
            'count' => $logs->count(),
        ]);
    }

    /**
     * Get settings compliance and security status.
     */
    public function complianceStatus(): JsonResponse
    {
        $this->authorize('create', Setting::class);

        // Check for critical system settings
        $criticalSettings = Setting::where('key', 'like', 'system.%')->get();
        $missingCritical = [];

        $requiredCritical = [
            'system.global_agent_pause',
            'system.maintenance_mode',
            'system.backup_enabled',
        ];

        foreach ($requiredCritical as $key) {
            if (! Setting::where('key', $key)->exists()) {
                $missingCritical[] = $key;
            }
        }

        // Check encryption status
        $sensitiveSettings = Setting::where('key', 'like', 'integrations.%_key')->get();
        $unencrypted = $sensitiveSettings->where('is_encrypted', false)->count();

        // Check for stale/old settings
        $thirtyDaysAgo = now()->subDays(30);
        $staleSettings = Setting::where('updated_at', '<', $thirtyDaysAgo)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'critical_settings' => [
                    'total_required' => count($requiredCritical),
                    'missing' => $missingCritical,
                    'status' => empty($missingCritical) ? 'compliant' : 'non-compliant',
                ],
                'encryption' => [
                    'total_sensitive' => $sensitiveSettings->count(),
                    'encrypted' => $sensitiveSettings->where('is_encrypted', true)->count(),
                    'unencrypted' => $unencrypted,
                    'status' => $unencrypted === 0 ? 'compliant' : 'non-compliant',
                ],
                'maintenance' => [
                    'stale_settings' => $staleSettings,
                    'status' => $staleSettings > 10 ? 'needs_review' : 'healthy',
                ],
            ],
        ]);
    }

    /**
     * Get multi-tenancy scope distribution.
     */
    public function multiTenancyStatus(): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $scopes = Setting::selectRaw('scope, count(*) as count')
            ->groupBy('scope')
            ->get();

        $workspaceSettings = Setting::where('scope', 'workspace')
            ->selectRaw('workspace_id, count(*) as count')
            ->groupBy('workspace_id')
            ->get();

        $userSettings = Setting::where('scope', 'user')
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'by_scope' => $scopes->mapWithKeys(fn ($s) => [$s->scope => $s->count])->all(),
                'workspace_distribution' => $workspaceSettings->map(fn ($w) => [
                    'workspace_id' => $w->workspace_id,
                    'setting_count' => $w->count,
                ])->all(),
                'user_distribution' => $userSettings->map(fn ($u) => [
                    'user_id' => $u->user_id,
                    'setting_count' => $u->count,
                ])->all(),
            ],
        ]);
    }

    /**
     * Get system performance metrics related to settings access.
     */
    public function performanceMetrics(): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $metrics = [
            'total_settings' => Setting::count(),
            'recent_changes' => Setting::where('updated_at', '>', now()->subDay())
                ->count(),
            'by_type' => Setting::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->mapWithKeys(fn ($t) => [$t->type => $t->count])
                ->all(),
            'cache_efficiency' => [
                'cached_keys' => cache()->tags('setting')->pull('keys') ?? [],
                'approx_cache_hits' => cache()->get('settings_cache_hits', 0),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Bulk export settings for backup or audit purposes.
     */
    public function exportSettings(Request $request): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $request->validate([
            'format' => ['sometimes', 'string', 'in:json,csv'],
            'include_encrypted' => ['sometimes', 'boolean'],
            'scope' => ['sometimes', 'string', 'in:global,workspace,user'],
        ]);

        $query = Setting::query();

        if ($request->has('scope')) {
            $query->where('scope', $request->input('scope'));
        }

        $settings = $query->get();

        // Mask or exclude encrypted values if not explicitly included
        if (! $request->input('include_encrypted', false)) {
            $settings = $settings->map(function ($setting) {
                if ($setting->is_encrypted) {
                    $setting->value = '[ENCRYPTED]';
                }

                return $setting;
            });
        }

        $format = $request->input('format', 'json');

        if ($format === 'csv') {
            // Build CSV format
            $csv = "key,type,group,scope,is_public,is_encrypted,value\n";
            foreach ($settings as $setting) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s",%d,%d,"%s"'."\n",
                    addcslashes($setting->key, '"'),
                    $setting->type,
                    $setting->group,
                    $setting->scope,
                    $setting->is_public ? 1 : 0,
                    $setting->is_encrypted ? 1 : 0,
                    addcslashes($setting->value, '"'),
                );
            }

            $this->logService->info('Settings exported as CSV', [
                'channel' => 'system',
                'type' => 'audit',
                'user_id' => $request->user()?->id,
                'context' => ['count' => $settings->count()],
            ]);

            return response()->json([
                'success' => true,
                'format' => 'csv',
                'data' => $csv,
            ]);
        }

        $this->logService->info('Settings exported as JSON', [
            'channel' => 'system',
            'type' => 'audit',
            'user_id' => $request->user()?->id,
            'context' => ['count' => $settings->count()],
        ]);

        return response()->json([
            'success' => true,
            'format' => 'json',
            'data' => $settings,
            'count' => $settings->count(),
        ]);
    }

    /**
     * Import settings from a JSON or CSV file.
     */
    public function importSettings(Request $request): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:json,csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $extension = $file->getClientOriginalExtension();

        $importedCount = 0;

        if ($extension === 'json') {
            $data = json_decode($content, true);
            if (! is_array($data)) {
                return response()->json(['success' => false, 'message' => 'Invalid JSON format'], 400);
            }

            foreach ($data as $item) {
                if (! isset($item['key'])) {
                    continue;
                }

                $setting = Setting::firstOrNew(['key' => $item['key']]);

                // Do not override encrypted values if they are masked in the export
                if ($item['value'] === '[ENCRYPTED]') {
                    continue;
                }

                $setting->fill([
                    'type' => $item['type'] ?? $setting->type ?? 'string',
                    'group' => $item['group'] ?? $setting->group ?? 'general',
                    'scope' => $item['scope'] ?? $setting->scope ?? 'global',
                    'is_public' => $item['is_public'] ?? $setting->is_public ?? false,
                    'value' => $item['value'] ?? '',
                ]);

                // If it was exported as encrypted, it needs re-encryption upon import if the system supports it,
                // but since we exported plain values or masked, we save the plain value. The observer/service will handle encryption if needed.
                $setting->save();
                $importedCount++;
            }
        } elseif ($extension === 'csv') {
            // Very simple CSV parsing
            $lines = explode("\n", $content);
            $headers = str_getcsv(array_shift($lines));

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                $row = str_getcsv($line);
                if (count($row) !== count($headers)) {
                    continue;
                }

                $item = array_combine($headers, $row);
                if (! isset($item['key']) || $item['value'] === '[ENCRYPTED]') {
                    continue;
                }

                $setting = Setting::firstOrNew(['key' => $item['key']]);
                $setting->fill([
                    'type' => $item['type'] ?? $setting->type ?? 'string',
                    'group' => $item['group'] ?? $setting->group ?? 'general',
                    'scope' => $item['scope'] ?? $setting->scope ?? 'global',
                    'is_public' => (bool) ($item['is_public'] ?? $setting->is_public ?? false),
                    'value' => $item['value'] ?? '',
                ]);
                $setting->save();
                $importedCount++;
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Unsupported file format'], 400);
        }

        $this->logService->info('Settings imported', [
            'channel' => 'system',
            'type' => 'audit',
            'user_id' => $request->user()?->id,
            'context' => ['count' => $importedCount, 'format' => $extension],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully imported $importedCount settings.",
            'count' => $importedCount,
        ]);
    }
}
