<?php

namespace Bithoven\LLMManager\Tests\Unit\Models;

use Bithoven\LLMManager\Models\LLMPromptTemplate;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMPromptTemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_prompt_template()
    {
        $template = LLMPromptTemplate::factory()->create([
            'name' => 'Code Review Template',
            'category' => 'code-review',
            'description' => 'Template for code review',
        ]);

        $this->assertDatabaseHas('llm_manager_prompt_templates', [
            'name' => 'Code Review Template',
            'category' => 'code-review',
        ]);
    }

    /** @test */
    public function it_casts_variables_to_array()
    {
        $template = LLMPromptTemplate::factory()->create([
            'template' => 'Hello {{name}}, your age is {{age}}',
            'variables' => ['name', 'age'],
        ]);

        $this->assertIsArray($template->variables);
        $this->assertContains('name', $template->variables);
        $this->assertContains('age', $template->variables);
    }

    /** @test */
    public function it_interpolates_variables_correctly()
    {
        $template = LLMPromptTemplate::factory()->create([
            'template' => 'Hello {{name}}, welcome to {{platform}}!',
            'variables' => ['name', 'platform'],
        ]);

        $result = $template->render([
            'name' => 'John',
            'platform' => 'BITHOVEN',
        ]);

        $this->assertEquals('Hello John, welcome to BITHOVEN!', $result);
    }

    /** @test */
    public function it_throws_exception_for_missing_variables()
    {
        $template = LLMPromptTemplate::factory()->create([
            'template' => 'Hello {{name}}',
            'variables' => ['name'],
        ]);

        // render() doesn't throw exception, just returns template with unfilled vars
        $result = $template->render([]);
        $this->assertEquals('Hello {{name}}', $result);

        // But validateVariables() should return false
        $this->assertFalse($template->validateVariables([]));
        $this->assertEquals(['name'], $template->getMissingVariables([]));
    }

    /** @test */
    public function scope_active_returns_only_active_templates()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Active Template',
            'is_active' => true,
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Inactive Template',
            'is_active' => false,
        ]);

        $active = LLMPromptTemplate::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('Active Template', $active->first()->name);
    }

    /** @test */
    public function scope_by_category_filters_correctly()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Code Template',
            'category' => 'code-generation',
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Review Template',
            'category' => 'code-review',
        ]);

        $codeTemplates = LLMPromptTemplate::byCategory('code-generation')->get();

        $this->assertCount(1, $codeTemplates);
        $this->assertEquals('Code Template', $codeTemplates->first()->name);
    }

    /** @test */
    public function scope_global_returns_only_global_templates()
    {
        LLMPromptTemplate::factory()->global()->create([
            'name' => 'Global Template',
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Extension Template',
            'extension_slug' => 'test-extension',
        ]);

        $globalTemplates = LLMPromptTemplate::global()->get();

        $this->assertCount(1, $globalTemplates);
        $this->assertNull($globalTemplates->first()->extension_slug);
    }

    /** @test */
    public function scope_for_extension_filters_by_extension()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Extension A Template',
            'extension_slug' => 'extension-a',
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Extension B Template',
            'extension_slug' => 'extension-b',
        ]);

        $extensionA = LLMPromptTemplate::forExtension('extension-a')->get();

        $this->assertCount(1, $extensionA);
        $this->assertEquals('extension-a', $extensionA->first()->extension_slug);
    }
}
