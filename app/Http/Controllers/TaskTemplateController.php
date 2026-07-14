<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use App\Services\Tasks\TaskTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskTemplateController extends Controller
{
    protected TaskTemplateService $templateService;

    public function __construct(TaskTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = TaskTemplate::orderBy('name')->get();

        return response()->json(['success' => true, 'data' => $templates]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'task_type' => 'required|string',
            'title_template' => 'required|string|max:255',
            'payload_template' => 'nullable|array',
            'expected_variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template = TaskTemplate::create($request->all());

        return response()->json(['success' => true, 'data' => $template], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskTemplate $taskTemplate)
    {
        return response()->json(['success' => true, 'data' => $taskTemplate]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskTemplate $taskTemplate)
    {
        $taskTemplate->update($request->all());

        return response()->json(['success' => true, 'data' => $taskTemplate]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskTemplate $taskTemplate)
    {
        $taskTemplate->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Spawn a task from the template.
     */
    public function spawn(Request $request, TaskTemplate $taskTemplate)
    {
        $variables = $request->input('variables', []);
        $overrides = $request->input('overrides', []);

        try {
            $task = $this->templateService->spawnTask($taskTemplate, $variables, $overrides);

            return response()->json([
                'success' => true,
                'message' => 'Task spawned successfully',
                'data' => $task,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
