<?php

namespace Bithoven\LLMManager\Services\Tools;

use Bithoven\LLMManager\Models\LLMToolDefinition;

class LLMToolService
{
    /**
     * Get tool by slug
     */
    public function get(string $slug): ?LLMToolDefinition
    {
        return LLMToolDefinition::where('slug', $slug)
            ->active()
            ->first();
    }

    /**
     * Get all tools for extension
     */
    public function getAllForExtension(?string $extensionSlug = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = LLMToolDefinition::active();

        if ($extensionSlug) {
            $query->byExtension($extensionSlug);
        }

        return $query->get();
    }

    /**
     * Get tools formatted for OpenAI function calling
     */
    public function getFormatted(?string $extensionSlug = null): array
    {
        $tools = $this->getAllForExtension($extensionSlug);

        return $tools->map(function ($tool) {
            return [
                'type' => 'function',
                'function' => [
                    'name' => $tool->slug,
                    'description' => $tool->description,
                    'parameters' => $tool->parameters_schema,
                ],
            ];
        })->toArray();
    }

    /**
     * Register a new tool
     */
    public function register(
        string $extensionSlug,
        string $toolType,
        string $name,
        string $description,
        array $parametersSchema,
        string $implementation,
        ?array $metadata = null
    ): LLMToolDefinition {
        $slug = \Str::slug($name);

        return LLMToolDefinition::create([
            'extension_slug' => $extensionSlug,
            'tool_type' => $toolType,
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'parameters_schema' => $parametersSchema,
            'implementation' => $implementation,
            'metadata' => $metadata,
            'is_active' => true,
        ]);
    }

    /**
     * Update tool
     */
    public function update(string $slug, array $data): LLMToolDefinition
    {
        $tool = $this->get($slug);

        if (!$tool) {
            throw new \Exception("Tool '{$slug}' not found");
        }

        $tool->update($data);

        return $tool->fresh();
    }

    /**
     * Delete tool
     */
    public function delete(string $slug): bool
    {
        $tool = $this->get($slug);

        if (!$tool) {
            return false;
        }

        return $tool->delete();
    }

    /**
     * Validate tool parameters
     */
    public function validateParameters(string $slug, array $parameters): bool
    {
        $tool = $this->get($slug);

        if (!$tool) {
            throw new \Exception("Tool '{$slug}' not found");
        }

        $schema = $tool->parameters_schema;

        // Simple validation (can be enhanced with JSON Schema validator)
        $required = $schema['required'] ?? [];

        foreach ($required as $param) {
            if (!isset($parameters[$param])) {
                throw new \Exception("Missing required parameter: {$param}");
            }
        }

        return true;
    }
}
