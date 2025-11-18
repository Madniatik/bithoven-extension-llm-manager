<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\Log;

/**
 * LLM Embeddings Service
 * 
 * Generates embeddings for text using LLM providers.
 * Currently uses deterministic mock embeddings for testing.
 * 
 * @package Bithoven\LLMManager\Services
 */
class LLMEmbeddingsService
{
    /**
     * Generate embedding for a single text
     * 
     * @param string $text Text to generate embedding for
     * @param string|null $model Model to use (default: from config)
     * @return array Vector embedding
     */
    public function generateEmbedding(string $text, ?string $model = null): array
    {
        // TODO: Implement actual API call to LLM provider
        // For now, return deterministic mock embedding based on text
        return $this->generateMockEmbedding($text);
    }

    /**
     * Generate embeddings for multiple texts in batch
     * 
     * @param array $texts Array of texts to generate embeddings for
     * @param string|null $model Model to use (default: from config)
     * @return array Array of vector embeddings
     */
    public function generateBatchEmbeddings(array $texts, ?string $model = null): array
    {
        $embeddings = [];
        
        foreach ($texts as $text) {
            $embeddings[] = $this->generateEmbedding($text, $model);
        }
        
        return $embeddings;
    }

    /**
     * Generate deterministic mock embedding for testing
     * 
     * Uses MD5 hash of text to generate consistent vector.
     * Embeddings are normalized to unit length.
     * 
     * @param string $text Input text
     * @return array 1536-dimensional normalized vector
     */
    protected function generateMockEmbedding(string $text): array
    {
        // Handle empty text
        if (empty(trim($text))) {
            return array_fill(0, 1536, 0.0);
        }

        // Generate deterministic seed from text
        $hash = md5($text);
        $seed = hexdec(substr($hash, 0, 8));
        mt_srand($seed);
        
        // Generate 1536-dimensional vector (OpenAI text-embedding-ada-002 size)
        $embedding = [];
        for ($i = 0; $i < 1536; $i++) {
            $embedding[] = mt_rand(-10000, 10000) / 10000;
        }
        
        // Normalize to unit vector
        return $this->normalize($embedding);
    }

    /**
     * Normalize vector to unit length
     * 
     * @param array $vector Input vector
     * @return array Normalized vector
     */
    protected function normalize(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $vector)));
        
        if ($magnitude == 0) {
            return $vector;
        }
        
        return array_map(fn($x) => $x / $magnitude, $vector);
    }

    /**
     * Calculate cosine similarity between two embeddings
     * 
     * @param array $embedding1 First embedding
     * @param array $embedding2 Second embedding
     * @return float Similarity score (0-1)
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        if (count($embedding1) !== count($embedding2)) {
            throw new \InvalidArgumentException('Embeddings must have the same dimensions');
        }
        
        $dotProduct = 0;
        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
        }
        
        return $dotProduct;
    }

    /**
     * Get embedding dimensions
     * 
     * @return int Number of dimensions
     */
    public function getDimensions(): int
    {
        return 1536;
    }

    /**
     * Check if embedding is normalized
     * 
     * @param array $embedding Embedding vector
     * @return bool True if normalized
     */
    public function isNormalized(array $embedding): bool
    {
        $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $embedding)));
        return abs($magnitude - 1.0) < 0.0001; // Allow small floating point error
    }
}
