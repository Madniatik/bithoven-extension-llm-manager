<?php

namespace Bithoven\LLMManager\Tests\Unit\Services;

use Bithoven\LLMManager\Services\LLMEmbeddingsService;
use Bithoven\LLMManager\Tests\TestCase;

class LLMEmbeddingsServiceTest extends TestCase
{
    protected LLMEmbeddingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LLMEmbeddingsService::class);
    }

    /** @test */
    public function it_generates_mock_embeddings()
    {
        $text = 'This is a test sentence for embedding generation.';
        
        $embedding = $this->service->generateEmbedding($text);

        $this->assertIsArray($embedding);
        $this->assertCount(1536, $embedding); // OpenAI dimensions
        
        // All values should be floats between -1 and 1
        foreach ($embedding as $value) {
            $this->assertIsFloat($value);
            $this->assertGreaterThanOrEqual(-1, $value);
            $this->assertLessThanOrEqual(1, $value);
        }
    }

    /** @test */
    public function it_generates_deterministic_embeddings()
    {
        $text = 'Deterministic test text';

        $embedding1 = $this->service->generateEmbedding($text);
        $embedding2 = $this->service->generateEmbedding($text);

        // Same text should produce same embedding
        $this->assertEquals($embedding1, $embedding2);
    }

    /** @test */
    public function it_generates_different_embeddings_for_different_texts()
    {
        $text1 = 'First text sample';
        $text2 = 'Second text sample';

        $embedding1 = $this->service->generateEmbedding($text1);
        $embedding2 = $this->service->generateEmbedding($text2);

        // Different texts should produce different embeddings
        $this->assertNotEquals($embedding1, $embedding2);
    }

    /** @test */
    public function it_handles_empty_text()
    {
        $embedding = $this->service->generateEmbedding('');

        $this->assertIsArray($embedding);
        $this->assertCount(1536, $embedding);
    }

    /** @test */
    public function it_handles_long_text()
    {
        $longText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 100);

        $embedding = $this->service->generateEmbedding($longText);

        $this->assertIsArray($embedding);
        $this->assertCount(1536, $embedding);
    }

    /** @test */
    public function it_handles_special_characters()
    {
        $text = 'Special chars: áéíóú ñ @#$%^&* 中文 العربية';

        $embedding = $this->service->generateEmbedding($text);

        $this->assertIsArray($embedding);
        $this->assertCount(1536, $embedding);
    }

    /** @test */
    public function it_generates_normalized_vectors()
    {
        $text = 'Test normalization';

        $embedding = $this->service->generateEmbedding($text);

        // Check that all values are within normalized range [-1, 1]
        foreach ($embedding as $value) {
            $this->assertGreaterThanOrEqual(-1, $value);
            $this->assertLessThanOrEqual(1, $value);
        }
    }

    /** @test */
    public function it_can_generate_batch_embeddings()
    {
        $texts = [
            'First document text',
            'Second document text',
            'Third document text',
        ];

        $embeddings = array_map(
            fn($text) => $this->service->generateEmbedding($text),
            $texts
        );

        $this->assertCount(3, $embeddings);
        
        foreach ($embeddings as $embedding) {
            $this->assertIsArray($embedding);
            $this->assertCount(1536, $embedding);
        }

        // Each embedding should be different
        $this->assertNotEquals($embeddings[0], $embeddings[1]);
        $this->assertNotEquals($embeddings[1], $embeddings[2]);
    }
}
