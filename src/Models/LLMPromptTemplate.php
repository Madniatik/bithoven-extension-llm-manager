<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LLMPromptTemplate extends Model
{
    use HasFactory;

    protected $table = 'llm_manager_prompt_templates';

    protected static function newFactory()
    {
        return \Bithoven\LLMManager\Database\Factories\LLMPromptTemplateFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'extension_slug',
        'category',
        'template',
        'variables',
        'example_values',
        'default_parameters',
        'is_active',
        'is_global',
        'description',
    ];

    protected $casts = [
        'variables' => 'array',
        'example_values' => 'array',
        'default_parameters' => 'array',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('extension_slug');
    }

    public function scopeForExtension($query, string $extensionSlug)
    {
        return $query->where('extension_slug', $extensionSlug);
    }

    /**
     * Render template with variables
     */
    public function render(array $values): string
    {
        $template = $this->template;

        foreach ($values as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }

        return $template;
    }

    /**
     * Validate that all required variables are provided
     */
    public function validateVariables(array $values): bool
    {
        foreach ($this->variables as $variable) {
            if (!array_key_exists($variable, $values)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing variables
     */
    public function getMissingVariables(array $values): array
    {
        return array_diff($this->variables, array_keys($values));
    }
}
