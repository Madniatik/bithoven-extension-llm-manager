<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LLMDocumentKnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'llm_document_knowledge_base';

    protected $fillable = [
        'extension_slug',
        'document_type',
        'title',
        'content',
        'content_chunks',
        'embeddings',
        'embedding_model',
        'metadata',
        'is_indexed',
        'indexed_at',
    ];

    protected $casts = [
        'content_chunks' => 'array',
        'embeddings' => 'array',
        'metadata' => 'array',
        'is_indexed' => 'boolean',
        'indexed_at' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeIndexed($query)
    {
        return $query->where('is_indexed', true);
    }

    public function scopeNotIndexed($query)
    {
        return $query->where('is_indexed', false);
    }

    public function scopeByExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeByDocumentType($query, string $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    /**
     * Mark document as indexed
     */
    public function markAsIndexed(): void
    {
        $this->is_indexed = true;
        $this->indexed_at = now();
        $this->save();
    }

    /**
     * Get chunk count
     */
    public function getChunkCountAttribute(): int
    {
        return is_array($this->content_chunks) ? count($this->content_chunks) : 0;
    }

    /**
     * Search similar documents by cosine similarity
     */
    public static function searchSimilar(array $queryEmbedding, int $topK = 5, string $extensionSlug = null): array
    {
        $query = static::indexed();

        if ($extensionSlug) {
            $query->byExtension($extensionSlug);
        }

        $documents = $query->get();
        $results = [];

        foreach ($documents as $doc) {
            if (!$doc->embeddings) {
                continue;
            }

            $similarity = static::cosineSimilarity($queryEmbedding, $doc->embeddings);
            $results[] = [
                'document' => $doc,
                'similarity' => $similarity,
            ];
        }

        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($results, 0, $topK);
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    protected static function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] ** 2;
            $magnitudeB += $b[$i] ** 2;
        }

        $magnitude = sqrt($magnitudeA) * sqrt($magnitudeB);

        return $magnitude > 0 ? $dotProduct / $magnitude : 0;
    }
}
