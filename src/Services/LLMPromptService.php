<?php

namespace Bithoven\LLMManager\Services;

use Bithoven\LLMManager\Models\LLMPromptTemplate;

class LLMPromptService
{
    /**
     * Render a template with variables
     */
    public function render(string $slug, array $variables): string
    {
        $template = LLMPromptTemplate::where('slug', $slug)
            ->active()
            ->firstOrFail();

        if (!$template->validateVariables($variables)) {
            $missing = $template->getMissingVariables($variables);
            throw new \Exception("Missing required variables: " . implode(', ', $missing));
        }

        return $template->render($variables);
    }

    /**
     * Get template by slug
     */
    public function get(string $slug): LLMPromptTemplate
    {
        return LLMPromptTemplate::where('slug', $slug)
            ->active()
            ->firstOrFail();
    }

    /**
     * Alias for get() - used by tests
     */
    public function getTemplate(string $slug): LLMPromptTemplate
    {
        return $this->get($slug);
    }

    /**
     * Validate template variables
     */
    public function validateVariables(string $slug, array $variables): bool
    {
        $template = $this->get($slug);
        return $template->validateVariables($variables);
    }

    /**
     * Get all templates for an extension
     */
    public function getByExtension(string $extensionSlug): \Illuminate\Database\Eloquent\Collection
    {
        return LLMPromptTemplate::byExtension($extensionSlug)
            ->active()
            ->get();
    }

    /**
     * Get templates by category
     */
    public function getByCategory(string $category, ?string $extensionSlug = null): \Illuminate\Database\Eloquent\Collection
    {
        return LLMPromptTemplate::byCategory($category)
            ->when($extensionSlug, fn($q) => $q->byExtension($extensionSlug))
            ->active()
            ->get();
    }

    /**
     * Create a new template
     */
    public function create(array $data): LLMPromptTemplate
    {
        // Auto-generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        // Extract variables from template
        if (!isset($data['variables'])) {
            $data['variables'] = $this->extractVariables($data['template']);
        }

        return LLMPromptTemplate::create($data);
    }

    /**
     * Update a template
     */
    public function update(string $slug, array $data): LLMPromptTemplate
    {
        $template = $this->get($slug);

        // Re-extract variables if template changed
        if (isset($data['template']) && $data['template'] !== $template->template) {
            $data['variables'] = $this->extractVariables($data['template']);
        }

        $template->update($data);

        return $template->fresh();
    }

    /**
     * Delete a template
     */
    public function delete(string $slug): bool
    {
        $template = $this->get($slug);

        return $template->delete();
    }

    /**
     * Extract variables from template
     */
    protected function extractVariables(string $template): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $template, $matches);

        return array_unique($matches[1]);
    }

    /**
     * Test a template with example values
     */
    public function test(string $slug): string
    {
        $template = $this->get($slug);

        $exampleValues = $template->example_values ?? [];

        if (empty($exampleValues)) {
            throw new \Exception('No example values defined for this template');
        }

        return $this->render($slug, $exampleValues);
    }
}
