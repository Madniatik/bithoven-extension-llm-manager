<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\RAG\LLMEmbeddingsService;

class LLMGenerateEmbeddingsCommand extends Command
{
    protected $signature = 'llm:generate-embeddings
                            {text : Text to generate embeddings for}';

    protected $description = 'Generate embeddings for text (testing)';

    public function handle(LLMEmbeddingsService $embeddingsService): int
    {
        $text = $this->argument('text');

        try {
            $this->info("Generating embeddings for: {$text}");
            
            $embeddings = $embeddingsService->generate($text);
            
            $this->info("✓ Embeddings generated successfully");
            $this->line("Dimensions: " . count($embeddings));
            $this->line("First 10 values: " . json_encode(array_slice($embeddings, 0, 10)));
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to generate embeddings: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
