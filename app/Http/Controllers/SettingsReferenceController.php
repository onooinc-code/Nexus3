<?php

namespace App\Http\Controllers;

use App\Services\SettingsReferenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsReferenceController extends Controller
{
    /**
     * @var SettingsReferenceService
     */
    protected $referenceService;

    /**
     * SettingsReferenceController constructor.
     *
     * @param SettingsReferenceService $referenceService
     */
    public function __construct(SettingsReferenceService $referenceService)
    {
        $this->referenceService = $referenceService;
    }

    /**
     * Display the developer reference dashboard for SettingsHub.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $apis = $this->referenceService->getApiEndpointsDocumentation();
        $services = $this->referenceService->getServicesDocumentation();
        $jobs = $this->referenceService->getJobsDocumentation();
        $metrics = $this->referenceService->getSystemMetrics();

        return view('hubs.settings-reference.index', compact(
            'apis',
            'services',
            'jobs',
            'metrics'
        ));
    }
}
