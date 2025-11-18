<?php

namespace Bithoven\LLMManager\Database\Factories;

use Bithoven\LLMManager\Models\LLMDocumentKnowledgeBase;
use Illuminate\Database\Eloquent\Factories\Factory;

class LLMDocumentKnowledgeBaseFactory extends Factory
{
    protected $model = LLMDocumentKnowledgeBase::class;

    public function definition(): array
    {
        $content = fake()->paragraphs(5, true);
        $chunks = $this->chunkContent($content);
        
        return [
            'extension_slug' => 'llm-manager',
            'document_type' => fake()->randomElement(['manual', 'faq', 'api_doc', 'code']),
            'title' => fake()->sentence(),
            'content' => $content,
            'content_chunks' => json_encode($chunks),
            'embeddings' => null,
            'embedding_model' => null,
            'metadata' => [
                'author' => fake()->name(),
                'version' => '1.0',
                'tags' => fake()->words(3),
            ],
            'is_indexed' => false,
            'indexed_at' => null,
        ];
    }

    protected function chunkContent(string $content, int $chunkSize = 500): array
    {
        $chunks = [];
        $sentences = explode('. ', $content);
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            if (strlen($currentChunk . $sentence) > $chunkSize) {
                if ($currentChunk) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $sentence . '. ';
            } else {
                $currentChunk .= $sentence . '. ';
            }
        }
        
        if ($currentChunk) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }

    public function indexed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_indexed' => true,
            'indexed_at' => now(),
            'embedding_model' => 'text-embedding-3-small',
            'embeddings' => json_encode(array_fill(0, 10, fake()->randomFloat(4, -1, 1))),
        ]);
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension_slug' => null,
        ]);
    }
}
