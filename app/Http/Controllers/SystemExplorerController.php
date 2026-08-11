<?php

namespace App\Http\Controllers;

use App\Services\SystemMetadataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemExplorerController extends Controller
{
    public function __construct(
        protected SystemMetadataService $metadataService
    ) {}

    /**
     * System Explorer Main Dashboard view
     */
    public function index(Request $request): View
    {
        return $this->renderView($request, 'dashboard');
    }

    /**
     * Project Routes Browser View
     */
    public function routesView(Request $request): View
    {
        return $this->renderView($request, 'routes');
    }

    /**
     * Database Schema Browser View
     */
    public function schemaView(Request $request): View
    {
        return $this->renderView($request, 'schema');
    }

    /**
     * Codebase (Controllers & Services) Browser View
     */
    public function codebaseView(Request $request): View
    {
        return $this->renderView($request, 'codebase');
    }

    /**
     * Project Documentation Browser View
     */
    public function docsView(Request $request): View
    {
        return $this->renderView($request, 'docs');
    }

    /**
     * Blade Views Explorer Browser View
     */
    public function viewsExplorer(Request $request): View
    {
        return $this->renderView($request, 'views');
    }

    /**
     * Render the unified interactive explorer view with active tab and data payload
     */
    private function renderView(Request $request, string $activeTab): View
    {
        $requestedDoc = $request->query('file', 'README.md');

        $routesData = $this->metadataService->getRoutes();
        $schemaData = $this->metadataService->getDatabaseSchema();
        $codebaseData = $this->metadataService->getControllersAndServices();
        $docsData = $this->metadataService->getDocumentationIndex($requestedDoc);
        $viewsData = $this->metadataService->getViews();

        return view('system.explorer', [
            'activeTab' => $activeTab,
            'routesData' => $routesData,
            'schemaData' => $schemaData,
            'codebaseData' => $codebaseData,
            'docsData' => $docsData,
            'viewsData' => $viewsData,
        ]);
    }
}
