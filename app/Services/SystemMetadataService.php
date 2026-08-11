<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;

class SystemMetadataService
{
    /**
     * Retrieve all current project routes categorized into API and WEB routes.
     *
     * @return array<string, mixed>
     */
    public function getRoutes(): array
    {
        $allRoutes = Route::getRoutes();

        $apiRoutes = [];
        $webRoutes = [];

        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            $middleware = $route->gatherMiddleware();
            $name = $route->getName();
            $action = $route->getActionName();
            $methods = array_values(array_diff($route->methods(), ['HEAD']));

            $routeData = [
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'action' => $action,
                'middleware' => array_values($middleware),
            ];

            if ($this->isApiRoute($uri, $middleware)) {
                $apiRoutes[] = $routeData;
            } else {
                $webRoutes[] = $routeData;
            }
        }

        return [
            'summary' => [
                'total' => count($allRoutes),
                'api_count' => count($apiRoutes),
                'web_count' => count($webRoutes),
            ],
            'api' => $apiRoutes,
            'web' => $webRoutes,
        ];
    }

    /**
     * Retrieve current database schema including tables, columns, indexes, and foreign keys.
     *
     * @return array<string, mixed>
     */
    public function getDatabaseSchema(): array
    {
        $tables = Schema::getTables();
        $schemaData = [];

        foreach ($tables as $table) {
            $tableName = $table['name'] ?? null;
            if (! $tableName) {
                continue;
            }

            $columns = Schema::getColumns($tableName);
            $indexes = Schema::getIndexes($tableName);
            $foreignKeys = Schema::getForeignKeys($tableName);

            $schemaData[] = [
                'name' => $tableName,
                'schema' => $table['schema'] ?? null,
                'size' => $table['size'] ?? null,
                'comment' => $table['comment'] ?? null,
                'columns_count' => count($columns),
                'columns' => $columns,
                'indexes_count' => count($indexes),
                'indexes' => $indexes,
                'foreign_keys_count' => count($foreignKeys),
                'foreign_keys' => $foreignKeys,
            ];
        }

        return [
            'tables_count' => count($schemaData),
            'tables' => $schemaData,
        ];
    }

    /**
     * Retrieve all Controllers and Services with their methods and descriptions.
     *
     * @return array<string, mixed>
     */
    public function getControllersAndServices(): array
    {
        $controllersPath = app_path('Http/Controllers');
        $servicesPath = app_path('Services');

        $controllers = $this->scanPhpClasses($controllersPath, 'App\\Http\\Controllers', 'app/Http/Controllers');
        $services = $this->scanPhpClasses($servicesPath, 'App\\Services', 'app/Services');

        return [
            'summary' => [
                'controllers_count' => count($controllers),
                'services_count' => count($services),
            ],
            'controllers' => $controllers,
            'services' => $services,
        ];
    }

    /**
     * Retrieve project README.md content and metadata.
     *
     * @return array<string, mixed>
     */
    public function getReadmeContent(): array
    {
        $readmePath = base_path('README.md');

        if (! File::exists($readmePath)) {
            return [
                'exists' => false,
                'content' => '',
                'path' => 'README.md',
            ];
        }

        $content = File::get($readmePath);

        return [
            'exists' => true,
            'filename' => 'README.md',
            'relative_path' => 'README.md',
            'size_bytes' => File::size($readmePath),
            'last_modified' => date('Y-m-d H:i:s', File::lastModified($readmePath)),
            'content' => $content,
        ];
    }

    /**
     * Retrieve all documentation files in the project along with optional file content.
     *
     * @return array<string, mixed>
     */
    public function getDocumentationIndex(?string $requestedFile = null): array
    {
        $docsDir = base_path('Documentation');
        $baseDir = base_path();

        $docsFiles = [];

        // Scan Documentation directory if exists
        if (File::isDirectory($docsDir)) {
            $allFiles = File::allFiles($docsDir);
            foreach ($allFiles as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['md', 'html', 'txt', 'mmd', 'json'])) {
                    $relPath = 'Documentation/'.str_replace('\\', '/', $file->getRelativePathname());
                    $docsFiles[] = $this->parseDocFileInfo($file->getRealPath(), $relPath);
                }
            }
        }

        // Scan root .md files
        $rootFiles = File::glob(base_path('*.md'));
        foreach ($rootFiles as $filePath) {
            $relPath = basename($filePath);
            $docsFiles[] = $this->parseDocFileInfo($filePath, $relPath);
        }

        // Sort docs by category and relative path
        usort($docsFiles, fn ($a, $b) => strcmp($a['relative_path'], $b['relative_path']));

        $activeDocContent = null;
        if ($requestedFile) {
            $safePath = base_path($requestedFile);
            if (File::exists($safePath) && str_starts_with(realpath($safePath), realpath($baseDir))) {
                $activeDocContent = File::get($safePath);
            }
        }

        return [
            'total_docs' => count($docsFiles),
            'files' => $docsFiles,
            'requested_file' => $requestedFile,
            'content' => $activeDocContent,
        ];
    }

    /**
     * Retrieve all Blade view templates in the project with purpose and directory structure.
     *
     * @return array<string, mixed>
     */
    public function getViews(): array
    {
        $viewsDir = resource_path('views');
        $viewsList = [];

        if (File::isDirectory($viewsDir)) {
            $files = File::allFiles($viewsDir);

            foreach ($files as $file) {
                $relPath = str_replace('\\', '/', $file->getRelativePathname());
                $dotName = str_replace(['/', '.blade.php'], ['.', ''], $relPath);
                $content = File::get($file->getRealPath());
                $description = $this->extractBladePurpose($content, $dotName);
                $parts = explode('/', $relPath);
                $category = count($parts) > 1 ? $parts[0] : 'root';

                $viewsList[] = [
                    'view_name' => $dotName,
                    'relative_path' => 'resources/views/'.$relPath,
                    'category' => $category,
                    'size_bytes' => $file->getSize(),
                    'purpose' => $description,
                ];
            }
        }

        usort($viewsList, fn ($a, $b) => strcmp($a['view_name'], $b['view_name']));

        return [
            'total_views' => count($viewsList),
            'views' => $viewsList,
        ];
    }

    /**
     * Helper to scan PHP classes in a directory.
     *
     * @return array<int, array<string, mixed>>
     */
    private function scanPhpClasses(string $directory, string $baseNamespace, string $relativePrefix): array
    {
        $classes = [];

        if (! File::isDirectory($directory)) {
            return $classes;
        }

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relPath = str_replace('\\', '/', $file->getRelativePathname());
            $classNameWithoutExt = str_replace(['/', '.php'], ['\\', ''], $relPath);
            $fqcn = $baseNamespace.'\\'.$classNameWithoutExt;

            try {
                if (! class_exists($fqcn, true)) {
                    continue;
                }

                $reflector = new ReflectionClass($fqcn);
                if ($reflector->isAbstract() || $reflector->isInterface() || $reflector->isTrait()) {
                    continue;
                }

                $docComment = $this->cleanDocComment($reflector->getDocComment() ?: '');
                $methods = [];

                foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    // Only methods declared in this class
                    if ($method->getDeclaringClass()->getName() !== $fqcn) {
                        continue;
                    }
                    if (str_starts_with($method->getName(), '__')) {
                        continue;
                    }

                    $params = [];
                    foreach ($method->getParameters() as $p) {
                        $params[] = [
                            'name' => '$'.$p->getName(),
                            'type' => $p->getType() ? (string) $p->getType() : 'mixed',
                            'optional' => $p->isOptional(),
                        ];
                    }

                    $methods[] = [
                        'name' => $method->getName(),
                        'return_type' => $method->getReturnType() ? (string) $method->getReturnType() : 'mixed',
                        'description' => $this->cleanDocComment($method->getDocComment() ?: ''),
                        'parameters' => $params,
                    ];
                }

                $classes[] = [
                    'class_name' => $reflector->getShortName(),
                    'fqcn' => $fqcn,
                    'relative_path' => $relativePrefix.'/'.$relPath,
                    'description' => $docComment ?: 'Handles '.$reflector->getShortName().' responsibilities.',
                    'methods_count' => count($methods),
                    'methods' => $methods,
                ];
            } catch (\Throwable $e) {
                // Skip unparseable classes
            }
        }

        usort($classes, fn ($a, $b) => strcmp($a['class_name'], $b['class_name']));

        return $classes;
    }

    /**
     * Helper to parse doc file info.
     *
     * @return array<string, mixed>
     */
    private function parseDocFileInfo(string $fullPath, string $relativePath): array
    {
        $content = File::get($fullPath);
        $title = basename($relativePath);

        // Extract title from first markdown header
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
        }

        $parts = explode('/', $relativePath);
        $category = count($parts) > 1 ? $parts[0] : 'Root Document';
        if (count($parts) > 2) {
            $category = $parts[0].' / '.$parts[1];
        }

        return [
            'title' => $title,
            'relative_path' => $relativePath,
            'category' => $category,
            'size_bytes' => filesize($fullPath),
            'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
        ];
    }

    /**
     * Helper to clean PHP DocBlock comments.
     */
    private function cleanDocComment(string $doc): string
    {
        if (! $doc) {
            return '';
        }

        $clean = preg_replace('/^\s*\/\*\*|\s*\*\/|\s*\* ?/m', '', $doc);
        $lines = explode("\n", $clean);
        $filtered = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '@')) {
                continue;
            }
            if ($trimmed !== '') {
                $filtered[] = $trimmed;
            }
        }

        return implode(' ', $filtered);
    }

    /**
     * Helper to extract Blade purpose description.
     */
    private function extractBladePurpose(string $content, string $viewName): string
    {
        if (preg_match('/\{\{--\s*(.*?)\s*--\}\}/s', $content, $matches)) {
            return trim(str_replace("\n", ' ', $matches[1]));
        }

        if (preg_match('/<!--\s*(.*?)\s*-->/s', $content, $matches)) {
            return trim(str_replace("\n", ' ', $matches[1]));
        }

        // Infer purpose from view name
        $nameParts = explode('.', $viewName);
        $action = end($nameParts);
        $domain = count($nameParts) > 1 ? $nameParts[0] : 'general';

        return ucfirst($domain).' hub view template for '.ucfirst($action).' UI component.';
    }

    /**
     * Determine if a route belongs to API based on URI or middleware.
     *
     * @param  array<string>  $middleware
     */
    private function isApiRoute(string $uri, array $middleware): bool
    {
        if (str_starts_with($uri, 'api/') || $uri === 'api') {
            return true;
        }

        foreach ($middleware as $m) {
            if (is_string($m) && (str_contains($m, 'api') || str_contains($m, 'Sanctum'))) {
                return true;
            }
        }

        return false;
    }
}
