<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProcessManager;
use App\Services\SystemMetadataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SystemController extends Controller
{
    protected $logsPath;

    public function __construct(
        protected ProcessManager $processManager,
        protected SystemMetadataService $metadataService
    ) {
        $this->logsPath = base_path('../logs');
    }

    /**
     * Get system status and all running processes (CACHED for 30 seconds)
     */
    public function status(): JsonResponse
    {
        try {
            $cacheKey = 'admin:system:status';

            // Check if we have a cached value
            if (Cache::has($cacheKey)) {
                $data = Cache::get($cacheKey);
                $data['cached'] = true;
                Log::debug('SystemController@status returning cached data');

                return response()->json($data);
            }

            // Generate fresh data if not cached
            Log::info('SystemController@status generating fresh data');
            $data = [
                'system' => $this->getSystemMetrics(),
                'services' => $this->getServicesStatus(),
                'timestamp' => now()->toIso8601String(),
                'cached' => false,
            ];

            // Cache for 30 seconds
            Cache::put($cacheKey, $data, 30);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('System status error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get detailed system metrics (optimized for speed)
     */
    private function getSystemMetrics(): array
    {
        try {
            return [
                'hostname' => gethostname() ?: 'unknown',
                'php_version' => phpversion() ?: 'unknown',
                'os' => PHP_OS,
                'memory' => [
                    'used' => memory_get_usage(true) / 1024 / 1024,
                    'limit' => ini_get('memory_limit') ?: 'unlimited',
                ],
                'disk' => [
                    'free_gb' => disk_free_space('/') / 1024 / 1024 / 1024,
                    'total_gb' => disk_total_space('/') / 1024 / 1024 / 1024,
                ],
                'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0],
                'uptime' => $this->getServerUptime(),
            ];
        } catch (\Exception $e) {
            Log::warning('Could not get system metrics: '.$e->getMessage());

            return [
                'hostname' => 'unknown',
                'php_version' => phpversion() ?: 'unknown',
                'os' => PHP_OS,
                'memory' => ['used' => 0, 'limit' => 'unknown'],
                'disk' => ['free_gb' => 0, 'total_gb' => 0],
                'load_average' => [0, 0, 0],
                'uptime' => 'unknown',
            ];
        }
    }

    /**
     * Get status of all backend services (REAL STATUS - not mocked)
     */
    private function getServicesStatus(): array
    {
        try {
            $statuses = $this->processManager->getServiceStatuses();

            $result = [];
            foreach ($statuses as $name => $status) {
                $result[$name] = [
                    'port' => $status['port'],
                    'status' => $status['status'],
                    'pid' => $status['pid'],
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning('Could not get service statuses: '.$e->getMessage());

            return [
                'api' => ['port' => 8000, 'status' => 'unknown', 'pid' => null],
                'reverb' => ['port' => 6001, 'status' => 'unknown', 'pid' => null],
                'nextjs' => ['port' => 3000, 'status' => 'unknown', 'pid' => null],
                'queue' => ['port' => null, 'status' => 'unknown', 'pid' => null],
                'vite' => ['port' => 5173, 'status' => 'unknown', 'pid' => null],
            ];
        }
    }

    /**
     * Get currently running processes
     */
    private function getRunningProcesses(): array
    {
        // This is now handled by ProcessManager
        return [];
    }

    /**
     * Start a specific service
     */
    public function startService(Request $request): JsonResponse
    {
        $service = $request->input('service');

        try {
            // Authorize
            if (! auth()->user()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $result = $this->processManager->startService($service);

            if (isset($result['error'])) {
                return response()->json($result, 400);
            }

            // Clear cache so next status request is fresh
            Cache::forget('admin:system:status');

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to start service: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stop a specific service
     */
    public function stopService(Request $request): JsonResponse
    {
        $service = $request->input('service');

        try {
            // Authorize
            if (! auth()->user()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $result = $this->processManager->stopService($service);

            if (isset($result['error'])) {
                return response()->json($result, 400);
            }

            // Clear cache so next status request is fresh
            Cache::forget('admin:system:status');

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to stop service: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restart a specific service
     */
    public function restartService(Request $request): JsonResponse
    {
        $service = $request->input('service');

        try {
            // Authorize
            if (! auth()->user()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $result = $this->processManager->restartService($service);

            if (isset($result['error'])) {
                return response()->json($result, 400);
            }

            // Clear cache so next status request is fresh
            Cache::forget('admin:system:status');

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to restart service: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get service logs
     */
    public function getServiceLogs(Request $request): JsonResponse
    {
        $service = $request->input('service', 'api');
        $lines = $request->input('lines', 100);

        try {
            // Authorize
            if (! auth()->user()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Ensure logs directory exists
            if (! is_dir($this->logsPath)) {
                return response()->json(['logs' => ['No logs available yet']]);
            }

            $logFile = $this->logsPath.DIRECTORY_SEPARATOR."{$service}.log";

            if (! File::exists($logFile)) {
                Log::warning("Log file not found: $logFile");

                return response()->json(['logs' => ["No logs available for service: $service"]]);
            }

            $content = File::get($logFile);
            $allLines = explode("\n", $content);
            $logLines = array_slice($allLines, -$lines);
            $logLines = array_filter($logLines, fn ($line) => trim($line) !== '');

            return response()->json(['logs' => array_values($logLines)]);
        } catch (\Exception $e) {
            Log::error('Failed to get service logs: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Trigger a build
     */
    public function triggerBuild(Request $request): JsonResponse
    {
        $type = $request->input('type', 'all'); // all, backend, frontend

        try {
            // Authorize
            if (! auth()->user()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Ensure logs directory exists
            if (! is_dir($this->logsPath)) {
                mkdir($this->logsPath, 0755, true);
            }

            $scriptPath = base_path('../build-fixed.ps1');
            if (! File::exists($scriptPath)) {
                $scriptPath = base_path('../build.ps1');
            }

            if (! File::exists($scriptPath)) {
                Log::warning('Build script not found');

                return response()->json(['error' => 'Build script not found'], 404);
            }

            // Run the build script
            $buildLogFile = $this->logsPath.DIRECTORY_SEPARATOR.'build-'.date('Y-m-d_H-i-s').'.log';

            Log::info('Triggering build', ['type' => $type, 'script' => $scriptPath]);

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: Use PowerShell with proper escaping
                $cmd = "powershell -ExecutionPolicy Bypass -Command \"& '$scriptPath' -BuildType '$type' *>> '$buildLogFile'\" &";
                shell_exec($cmd);
            } else {
                // Linux/Mac: Use bash equivalent
                $bashScript = str_replace('.ps1', '.sh', $scriptPath);
                if (File::exists($bashScript)) {
                    shell_exec("bash '$bashScript' $type >> $buildLogFile 2>&1 &");
                } else {
                    return response()->json(['error' => 'Build script not found for this platform'], 404);
                }
            }

            Log::info('Build triggered successfully', ['type' => $type]);

            return response()->json([
                'status' => 'started',
                'message' => "Build ($type) started in background",
                'log_file' => $buildLogFile,
                'type' => $type,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to trigger build: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if a port is in use
     */
    private function isPortInUse(int $port): bool
    {
        try {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($socket) {
                fclose($socket);

                return true;
            }
        } catch (\Exception $e) {
            // Port not in use
        }

        return false;
    }

    /**
     * Check if a process is running
     */
    private function isProcessRunning(int $pid): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec("tasklist /FI \"PID eq $pid\" 2>NUL");

            return $output !== null && strpos($output, (string) $pid) !== false;
        } else {
            return posix_kill($pid, 0);
        }
    }

    /**
     * Get server uptime
     */
    private function getServerUptime(): string
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Try wmic first (may not be available on all Windows systems)
                $output = @shell_exec('wmic os get lastbootuptime 2>&1');
                if ($output && strpos($output, 'wmic') === false) {
                    preg_match('/(\d{14})/', $output, $matches);
                    if (! empty($matches)) {
                        $bootTime = \DateTime::createFromFormat('YmdHis', $matches[1]);
                        if ($bootTime) {
                            $uptime = now()->diffInSeconds($bootTime);

                            return $this->formatUptime($uptime);
                        }
                    }
                }

                // Fallback: return current session start time or "unknown"
                return 'unknown';
            } else {
                $output = @shell_exec('uptime 2>&1');
                if ($output) {
                    preg_match('/up\s+(.+),\s+\d+\s+user/', $output, $matches);
                    if (! empty($matches)) {
                        return trim($matches[1]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Unable to fetch uptime: '.$e->getMessage());
        }

        return 'unknown';
    }

    /**
     * Format uptime seconds
     */
    private function formatUptime(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return "{$days}d {$hours}h {$minutes}m";
    }

    /**
     * Get all project routes (categorized by API and WEB)
     */
    public function routes(): JsonResponse
    {
        try {
            $data = $this->metadataService->getRoutes();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system routes: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current database schema
     */
    public function schema(): JsonResponse
    {
        try {
            $data = $this->metadataService->getDatabaseSchema();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get database schema: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all project Controllers and Services with their methods and descriptions
     */
    public function codebase(): JsonResponse
    {
        try {
            $data = $this->metadataService->getControllersAndServices();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get codebase metadata: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get project documentation index and contents
     */
    public function docs(Request $request): JsonResponse
    {
        try {
            $file = $request->query('file');
            $data = $this->metadataService->getDocumentationIndex($file);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get documentation index: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all Blade views templates and their purposes
     */
    public function views(): JsonResponse
    {
        try {
            $data = $this->metadataService->getViews();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get views metadata: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return README.md content as JSON, raw text, or web view.
     *
     * @return JsonResponse|Response|View
     */
    public function readme(Request $request)
    {
        try {
            $data = $this->metadataService->getReadmeContent();
            $format = strtolower((string) $request->query('format', ''));

            if ($format === 'raw' || $format === 'text') {
                return response($data['content'], 200, [
                    'Content-Type' => 'text/plain; charset=UTF-8',
                ]);
            }

            if ($request->wantsJson() || $format === 'json') {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            }

            return view('system.readme', ['readme' => $data]);
        } catch (\Exception $e) {
            Log::error('Failed to get README metadata: '.$e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->query('format') === 'json') {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }

            return response($e->getMessage(), 500);
        }
    }

    /**
     * Clear all caches (browser & server), clear views/routes/config/events, and run full Laravel optimization.
     */
    public function optimizeAndClear(): JsonResponse
    {
        try {
            $steps = [];

            $runCmd = function (string $command, string $label) use (&$steps) {
                Artisan::call($command);
                $rawOut = trim(Artisan::output());
                $steps[] = [
                    'command' => 'php artisan '.$command,
                    'label' => $label,
                    'output' => $rawOut ?: 'Command executed cleanly.',
                    'status' => 'success',
                ];
            };

            // 1. Clear application cache
            $runCmd('cache:clear', 'Clear Application Cache');

            // 2. Clear route cache
            $runCmd('route:clear', 'Clear Route Cache');

            // 3. Clear config cache
            $runCmd('config:clear', 'Clear Configuration Cache');

            // 4. Clear compiled views
            $runCmd('view:clear', 'Clear Compiled Blade Views');

            // 5. Clear event cache
            $runCmd('event:clear', 'Clear Event Cache');

            // 6. Purge optimization state and compile framework optimization
            $runCmd('optimize:clear', 'Purge Optimization Bootstrap');
            $runCmd('optimize', 'Compile Framework Optimization');

            // 7. Flush Redis and system cache
            Cache::flush();
            $steps[] = [
                'command' => 'Cache::flush()',
                'label' => 'Flush Redis & Memory Cache',
                'output' => 'Flushed Redis keys and application cache store.',
                'status' => 'success',
            ];

            Log::info('System optimize and clear executed with detailed outputs');

            return response()->json([
                'success' => true,
                'message' => 'System cache, routes, views, config, and optimization completed successfully.',
                'steps' => $steps,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('System optimize and clear failed: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get live queue details and Redis metrics
     */
    public function queueDetails(): JsonResponse
    {
        try {
            $redis = Redis::connection('default');
            $queues = ['default', 'critical', 'llm-inference', 'batch', 'agent-tasks'];
            $queueStats = [];

            foreach ($queues as $q) {
                $size = (int) $redis->llen("queues:{$q}");
                $queueStats[] = [
                    'name' => $q,
                    'size' => $size,
                    'status' => $size > 0 ? 'processing' : 'idle',
                ];
            }

            $failedJobsCount = DB::table('failed_jobs')->count();
            $totalJobs = array_sum(array_column($queueStats, 'size'));
            $masters = $redis->keys('*horizon:master:*');
            $horizonStatus = count($masters) > 0 ? 'running' : 'idle';

            return response()->json([
                'success' => true,
                'status' => $horizonStatus,
                'total_jobs' => $totalJobs,
                'failed_jobs' => $failedJobsCount,
                'queues' => $queueStats,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'offline',
                'total_jobs' => 0,
                'failed_jobs' => 0,
                'queues' => [],
            ]);
        }
    }
}
