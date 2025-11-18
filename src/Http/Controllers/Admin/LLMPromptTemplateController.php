<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMPromptTemplate;

class LLMPromptTemplateController extends Controller
{
    public function index()
    {
        $templates = LLMPromptTemplate::orderBy('category')
            ->orderBy('name')
            ->get();

        return view('llm-manager::admin.prompts.index', compact('templates'));
    }

    public function create()
    {
        $categories = LLMPromptTemplate::distinct('category')
            ->pluck('category')
            ->filter();

        return view('llm-manager::admin.prompts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'template' => 'required|string',
            'description' => 'nullable|string',
            'extension_slug' => 'nullable|string',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $template = LLMPromptTemplate::create($validated);

        return redirect()
            ->route('admin.llm.prompts.show', $template)
            ->with('success', 'Template created successfully');
    }

    public function show(LLMPromptTemplate $template)
    {
        return view('llm-manager::admin.prompts.show', compact('template'));
    }

    public function edit(LLMPromptTemplate $template)
    {
        $categories = LLMPromptTemplate::distinct('category')
            ->pluck('category')
            ->filter();

        return view('llm-manager::admin.prompts.edit', compact('template', 'categories'));
    }

    public function update(Request $request, LLMPromptTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'template' => 'required|string',
            'description' => 'nullable|string',
            'extension_slug' => 'nullable|string',
        ]);

        $template->update($validated);

        return redirect()
            ->route('admin.llm.prompts.show', $template)
            ->with('success', 'Template updated successfully');
    }

    public function destroy(LLMPromptTemplate $template)
    {
        $template->delete();

        return redirect()
            ->route('admin.llm.prompts.index')
            ->with('success', 'Template deleted successfully');
    }
}
