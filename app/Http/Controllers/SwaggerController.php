<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class SwaggerController extends Controller
{
    /**
     * Display Swagger UI documentation
     *
     * @return Response
     */
    public function ui()
    {
        $swaggerUiPath = base_path('Documentation/Api/swagger-ui.html');

        if (! File::exists($swaggerUiPath)) {
            return response()->json([
                'error' => 'Swagger UI not found',
                'path' => $swaggerUiPath,
            ], 404);
        }

        return response(File::get($swaggerUiPath), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Serve OpenAPI specification
     *
     * @return JsonResponse
     */
    public function spec()
    {
        $specPath = base_path('Documentation/Api/openapi.json');

        if (! File::exists($specPath)) {
            return response()->json([
                'error' => 'OpenAPI specification not found',
                'path' => $specPath,
            ], 404);
        }

        $spec = json_decode(File::get($specPath), true);

        // Dynamically set the correct base URL
        $spec['servers'] = [
            [
                'url' => config('app.url').'/api/v1',
                'description' => 'Current Server',
            ],
        ];

        return response()->json($spec)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    /**
     * ReDoc documentation
     *
     * @return Response
     */
    public function redoc()
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Nexus Platform API - ReDoc</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <redoc spec-url='/openapi.json'></redoc>
    <script src="https://cdn.jsdelivr.net/npm/redoc@latest/bundles/redoc.standalone.js"></script>
</body>
</html>
HTML;

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Display Tasks Hub Swagger UI
     */
    public function tasksHubUi()
    {
        $path = public_path('tasks-hub-swagger.html');
        if (! File::exists($path)) {
            $path = base_path('Documentation/Api/tasks-hub-swagger.html');
        }

        return response(File::get($path), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Serve Tasks Hub OpenAPI JSON Spec
     */
    public function tasksHubSpec()
    {
        $path = public_path('tasks-hub-openapi.json');
        if (! File::exists($path)) {
            $path = base_path('Documentation/Api/tasks-hub-openapi.json');
        }

        $spec = json_decode(File::get($path), true);
        $spec['servers'] = [
            [
                'url' => config('app.url').'/api/v1',
                'description' => 'Current Server',
            ],
        ];

        return response()->json($spec)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    /**
     * Serve Tasks Hub OpenAPI JS Export
     */
    public function tasksHubJs()
    {
        $path = public_path('tasks-hub-openapi.js');

        return response(File::get($path), 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
