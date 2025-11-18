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
            'parameters_schema' => 'required|array',
            'implementation' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $tool = $toolService->register(
            $validated['extension_slug'],
            $validated['type'],
            $validated['name'],
            $validated['description'],
            $validated['parameters_schema'],
            $validated['implementation'],
            $validated['metadata'] ?? null
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
            'parameters_schema' => 'required|array',
            'implementation' => 'required|string',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

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
