<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\LLMRAGService;
use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;

class LLMIndexDocumentsCommand extends Command
{
    protected $signature = 'llm:index-documents
                            {--extension= : Index only documents for specific extension}
                            {--document= : Index specific document by ID}
                            {--force : Re-index already indexed documents}';

    protected $description = 'Index documents in knowledge base for RAG';

    public function handle(LLMRAGService $ragService): int
    {
        $extensionSlug = $this->option('extension');
        $documentId = $this->option('document');
        $force = $this->option('force');

        if ($documentId) {
            return $this->indexSingle($ragService, $documentId);
        }

        return $this->indexBulk($ragService, $extensionSlug, $force);
    }

    protected function indexSingle(LLMRAGService $ragService, int $documentId): int
    {
        try {
            $this->info("Indexing document ID: {$documentId}");
            
            $ragService->indexDocument($documentId);
            
            $this->info('✓ Document indexed successfully');
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to index document: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function indexBulk(LLMRAGService $ragService, ?string $extensionSlug, bool $force): int
    {
        $query = LLMDocumentKnowledgeBase::query();

        if (!$force) {
            $query->notIndexed();
        }

        if ($extensionSlug) {
            $query->byExtension($extensionSlug);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No documents to index.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} document(s) to index...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $indexed = 0;
        $failed = 0;

        foreach ($query->cursor() as $document) {
            try {
                $ragService->indexDocument($document->id);
                $indexed++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to index document {$document->id}: {$e->getMessage()}");
                $failed++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Indexed: {$indexed}");
        if ($failed > 0) {
            $this->warn("✗ Failed: {$failed}");
        }

        return self::SUCCESS;
    }
}
