<?php

namespace Bithoven\LLMManager\Services\RAG;

use Illuminate\Support\Facades\App;
use Bithoven\LLMManager\Services\Providers\OpenAIProvider;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMEmbeddingsService
{
    /**
     * Generate embeddings for text
     */
    public function generate(string $text): array
    {
        // Use configured embedding provider
        $provider = config('llm-manager.rag.embedding_provider', 'openai');

        return match ($provider) {
            'openai' => $this->generateOpenAI($text),
            default => throw new \Exception("Unsupported embedding provider: {$provider}"),
        };
    }

    /**
     * Generate embeddings using OpenAI
     */
    protected function generateOpenAI(string $text): array
    {
        // Get OpenAI configuration
        $config = LLMConfiguration::where('provider', 'openai')
            ->active()
            ->first();

        if (!$config) {
            // Return mock embeddings for development/testing
            return $this->generateMockEmbedding($text);
        }

        $provider = new OpenAIProvider($config);

        return $provider->embed($text);
    }

    /**
     * Generate mock embedding for testing (when no OpenAI config available)
     */
    protected function generateMockEmbedding(string $text): array
    {
        // Generate simple deterministic embedding based on text hash
        $hash = md5($text);
        $dimensions = 1536; // OpenAI embedding dimensions
        $embedding = [];

        for ($i = 0; $i < $dimensions; $i++) {
            // Use hash bytes to generate pseudo-random but deterministic values
            $byte = hexdec(substr($hash, $i % 32, 2));
            $embedding[] = ($byte / 255) * 2 - 1; // Normalize to [-1, 1]
        }

        return $embedding;
    }

    /**
     * Batch generate embeddings
     */
    public function batchGenerate(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $embeddings[] = $this->generate($text);
        }

        return $embeddings;
    }

    /**
     * Calculate cosine similarity between two embeddings
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        return \Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase::cosineSimilarity(
            $embedding1,
            $embedding2
        );
    }
}
