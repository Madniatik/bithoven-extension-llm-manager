<?php

namespace Bithoven\LLMManager\Services\RAG;

use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

class LLMRAGService
{
    public function __construct(
        protected LLMEmbeddingsService $embeddingsService
    ) {
    }

    /**
     * Search documents by semantic similarity
     */
    public function search(string $query, ?string $extensionSlug = null, int $topK = 5): array
    {
        // Generate query embedding
        $queryEmbedding = $this->embeddingsService->generate($query);

        // Search similar documents
        $results = LLMDocumentKnowledgeBase::searchSimilar(
            $queryEmbedding,
            $topK,
            $extensionSlug
        );

        return array_map(function ($result) {
            return [
                'id' => $result['document']->id,
                'title' => $result['document']->title,
                'content' => $result['document']->content,
                'similarity' => $result['similarity'],
                'metadata' => $result['document']->metadata,
            ];
        }, $results);
    }

    /**
     * Index a document
     */
    public function indexDocument(int $documentId): void
    {
        $document = LLMDocumentKnowledgeBase::findOrFail($documentId);

        if ($document->is_indexed) {
            return;
        }

        // Chunk content
        $chunks = $this->chunkContent($document->content);
        $document->content_chunks = $chunks;

        // Generate embeddings for each chunk
        $embeddings = [];
        foreach ($chunks as $chunk) {
            $embeddings[] = $this->embeddingsService->generate($chunk);
        }

        // Average embeddings (or use first chunk for simplicity)
        $document->embeddings = $embeddings[0] ?? [];
        $document->embedding_model = config('llm-manager.rag.embedding_model', 'text-embedding-3-small');

        $document->markAsIndexed();
    }

    /**
     * Chunk content into smaller pieces
     */
    protected function chunkContent(string $content): array
    {
        $chunkSize = config('llm-manager.rag.chunking.size', 1000);
        $overlap = config('llm-manager.rag.chunking.overlap', 200);
        $strategy = config('llm-manager.rag.chunking.strategy', 'semantic');

        if ($strategy === 'semantic') {
            return $this->semanticChunking($content, $chunkSize, $overlap);
        }

        return $this->fixedChunking($content, $chunkSize, $overlap);
    }

    /**
     * Fixed-size chunking
     */
    protected function fixedChunking(string $content, int $chunkSize, int $overlap): array
    {
        $chunks = [];
        $position = 0;
        $contentLength = strlen($content);

        while ($position < $contentLength) {
            $chunk = substr($content, $position, $chunkSize);
            $chunks[] = $chunk;
            $position += $chunkSize - $overlap;
        }

        return $chunks;
    }

    /**
     * Semantic chunking (by paragraphs/sentences)
     */
    protected function semanticChunking(string $content, int $targetSize, int $overlap): array
    {
        // Split by paragraphs
        $paragraphs = preg_split('/\n\n+/', $content);
        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $paragraph) {
            if (strlen($currentChunk) + strlen($paragraph) > $targetSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                // Add overlap from end of previous chunk
                $overlapText = substr($currentChunk, -$overlap);
                $currentChunk = $overlapText . ' ' . $paragraph;
            } else {
                $currentChunk .= ($currentChunk ? "\n\n" : '') . $paragraph;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Bulk index documents
     */
    public function bulkIndex(?string $extensionSlug = null): int
    {
        $query = LLMDocumentKnowledgeBase::notIndexed();

        if ($extensionSlug) {
            $query->byExtension($extensionSlug);
        }

        $documents = $query->get();
        $indexed = 0;

        foreach ($documents as $document) {
            try {
                $this->indexDocument($document->id);
                $indexed++;
            } catch (\Exception $e) {
                \Log::error("Failed to index document {$document->id}: {$e->getMessage()}");
            }
        }

        return $indexed;
    }

    /**
     * Add document to knowledge base
     */
    public function addDocument(
        string $extensionSlug,
        string $documentType,
        string $title,
        string $content,
        ?array $metadata = null
    ): LLMDocumentKnowledgeBase {
        $document = LLMDocumentKnowledgeBase::create([
            'extension_slug' => $extensionSlug,
            'document_type' => $documentType,
            'title' => $title,
            'content' => $content,
            'metadata' => $metadata,
        ]);

        // Auto-index if configured
        if (config('llm-manager.rag.auto_index', true)) {
            $this->indexDocument($document->id);
        }

        return $document;
    }

    /**
     * Search and generate answer (RAG pipeline)
     */
    public function generateAnswer(string $query, ?string $extensionSlug = null): array
    {
        // Search relevant documents
        $documents = $this->search($query, $extensionSlug);

        if (empty($documents)) {
            return [
                'answer' => 'No relevant information found in the knowledge base.',
                'sources' => [],
            ];
        }

        // Build context from top results
        $context = implode("\n\n", array_map(
            fn($doc) => "Source: {$doc['title']}\n{$doc['content']}",
            array_slice($documents, 0, 3)
        ));

        // Generate answer using LLM with context
        $prompt = "Based on the following information:\n\n{$context}\n\nAnswer the question: {$query}";

        $llmManager = app(\Bithoven\LLMManager\Services\LLMManager::class);
        $result = $llmManager->generate($prompt);

        return [
            'answer' => $result['response'],
            'sources' => array_map(fn($doc) => [
                'title' => $doc['title'],
                'similarity' => $doc['similarity'],
            ], $documents),
            'usage' => $result['usage'],
        ];
    }
}
