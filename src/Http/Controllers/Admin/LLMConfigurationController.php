<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMConfigurationController extends Controller
{
    public function index()
    {
        $configurations = LLMConfiguration::with('statistics')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('llm-manager::admin.configurations.index', compact('configurations'));
    }

    public function create()
    {
        $providers = config('llm-manager.providers', []);
        
        return view('llm-manager::admin.configurations.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string',
            'model' => 'required|string',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|url',
            'parameters' => 'nullable|array',
            'max_tokens' => 'nullable|integer|min:1',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $configuration = LLMConfiguration::create($validated);

        return redirect()
            ->route('admin.llm.configurations.show', $configuration)
            ->with('success', 'Configuration created successfully');
    }

    public function show(LLMConfiguration $configuration)
    {
        $configuration->load(['statistics', 'usageLogs' => function($q) {
            $q->latest()->limit(50);
        }]);

        return view('llm-manager::admin.configurations.show', compact('configuration'));
    }

    public function edit(LLMConfiguration $configuration)
    {
        $providers = config('llm-manager.providers', []);
        
        return view('llm-manager::admin.configurations.edit', compact('configuration', 'providers'));
    }

    public function update(Request $request, LLMConfiguration $configuration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|url',
            'parameters' => 'nullable|array',
            'max_tokens' => 'nullable|integer|min:1',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'is_active' => 'boolean',
        ]);

        $configuration->update($validated);

        return redirect()
            ->route('admin.llm.configurations.show', $configuration)
            ->with('success', 'Configuration updated successfully');
    }

    public function destroy(LLMConfiguration $configuration)
    {
        $configuration->delete();

        return redirect()
            ->route('admin.llm.configurations.index')
            ->with('success', 'Configuration deleted successfully');
    }

    public function toggleActive(LLMConfiguration $configuration)
    {
        $configuration->is_active = !$configuration->is_active;
        $configuration->save();

        return back()->with('success', 'Configuration status updated');
    }
}
