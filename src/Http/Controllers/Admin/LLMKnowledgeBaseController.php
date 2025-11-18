<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;
use Bithoven\LLMManager\Services\LLMRAGService;

class LLMKnowledgeBaseController extends Controller
{
    public function index()
    {
        $documents = LLMDocumentKnowledgeBase::orderBy('is_indexed', 'desc')
            ->latest()
            ->paginate(20);

        return view('llm-manager::admin.knowledge-base.index', compact('documents'));
    }

    public function create()
    {
        $types = ['documentation', 'guide', 'faq', 'code', 'other'];
        
        return view('llm-manager::admin.knowledge-base.create', compact('types'));
    }

    public function store(Request $request, LLMRAGService $ragService)
    {
        $validated = $request->validate([
            'extension_slug' => 'required|string',
            'document_type' => 'required|string',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $document = $ragService->addDocument(
            $validated['extension_slug'],
            $validated['document_type'],
            $validated['title'],
            $validated['content'],
            $validated['metadata'] ?? null
        );

        return redirect()
            ->route('admin.llm.knowledge-base.show', $document)
            ->with('success', 'Document added successfully');
    }

    public function show(LLMDocumentKnowledgeBase $document)
    {
        return view('llm-manager::admin.knowledge-base.show', compact('document'));
    }

    public function edit(LLMDocumentKnowledgeBase $document)
    {
        $types = ['documentation', 'guide', 'faq', 'code', 'other'];
        
        return view('llm-manager::admin.knowledge-base.edit', compact('document', 'types'));
    }

    public function update(Request $request, LLMDocumentKnowledgeBase $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'document_type' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $document->update($validated);

        // Re-index if content changed
        if ($document->wasChanged('content')) {
            $document->is_indexed = false;
            $document->save();
        }

        return redirect()
            ->route('admin.llm.knowledge-base.show', $document)
            ->with('success', 'Document updated successfully');
    }

    public function destroy(LLMDocumentKnowledgeBase $document)
    {
        $document->delete();

        return redirect()
            ->route('admin.llm.knowledge-base.index')
            ->with('success', 'Document deleted successfully');
    }

    public function indexDocument(LLMDocumentKnowledgeBase $document, LLMRAGService $ragService)
    {
        try {
            $ragService->indexDocument($document->id);
            return back()->with('success', 'Document indexed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to index document: ' . $e->getMessage());
        }
    }
}
