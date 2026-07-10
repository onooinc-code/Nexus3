<?php

namespace App\Http\Controllers;

use App\Services\SettingsReferenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsReferenceController extends Controller
{
    /**
     * SettingsReferenceController constructor.
     */
    public function __construct(
        protected SettingsReferenceService $referenceService
    ) {}

    /**
     * Display the developer reference dashboard for SettingsHub.
     */
    public function index(Request $request): View
    {
        $apis = $this->referenceService->getApiEndpointsDocumentation();
        $services = $this->referenceService->getServicesDocumentation();
        $jobs = $this->referenceService->getJobsDocumentation();
        $metrics = $this->referenceService->getSystemMetrics();
        $stats = $this->referenceService->getStatistics();

        return view('hubs.settings-reference.index', compact(
            'apis',
            'services',
            'jobs',
            'metrics',
            'stats'
        ));
    }
}
