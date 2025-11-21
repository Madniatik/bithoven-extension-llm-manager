<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMToolDefinition;
use Bithoven\LLMManager\Services\Tools\LLMToolService;

class LLMToolDefinitionController extends Controller
{
    public function index()
    {
        $tools = LLMToolDefinition::orderBy('type')
            ->orderBy('name')
            ->get();

        return view('llm-manager::admin.tools.index', compact('tools'));
    }

    public function create()
    {
        $types = ['function_calling', 'mcp'];
        
        return view('llm-manager::admin.tools.create', compact('types'));
    }

    public function store(Request $request, LLMToolService $toolService)
    {
        $validated = $request->validate([
            'extension_slug' => 'required|string',
            'type' => 'required|in:function_calling,mcp',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'parameters' => 'required|json',  // Changed from parameters_schema|array to parameters|json
            'implementation' => 'required|string',
            'metadata' => 'nullable|json',  // Changed from array to json
        ]);

        // Parse JSON strings to arrays
        $parametersSchema = json_decode($validated['parameters'], true);
        $metadata = !empty($validated['metadata']) ? json_decode($validated['metadata'], true) : null;

        $tool = $toolService->register(
            $validated['extension_slug'],
            $validated['type'],
            $validated['name'],
            $validated['description'],
            $parametersSchema,
            $validated['implementation'],
            $metadata
        );

        return redirect()
            ->route('admin.llm.tools.show', $tool)
            ->with('success', 'Tool registered successfully');
    }

    public function show(LLMToolDefinition $tool)
    {
        return view('llm-manager::admin.tools.show', compact('tool'));
    }

    public function edit(LLMToolDefinition $tool)
    {
        $types = ['function_calling', 'mcp'];
        
        return view('llm-manager::admin.tools.edit', compact('tool', 'types'));
    }

    public function update(Request $request, LLMToolDefinition $tool, LLMToolService $toolService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'parameters' => 'required|json',
            'implementation' => 'required|string',
            'metadata' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        // Parse JSON to arrays
        $validated['parameters_schema'] = json_decode($validated['parameters'], true);
        $validated['metadata'] = !empty($validated['metadata']) ? json_decode($validated['metadata'], true) : null;
        unset($validated['parameters']); // Remove the JSON string version

        $toolService->update($tool->slug, $validated);

        return redirect()
            ->route('admin.llm.tools.show', $tool)
            ->with('success', 'Tool updated successfully');
    }

    public function destroy(LLMToolDefinition $tool, LLMToolService $toolService)
    {
        $toolService->delete($tool->slug);

        return redirect()
            ->route('admin.llm.tools.index')
            ->with('success', 'Tool deleted successfully');
    }
}
