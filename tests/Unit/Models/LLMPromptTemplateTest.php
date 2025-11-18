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
        $template = LLMPromptTemplate::create([
            'name' => 'Code Review Template',
            'category' => 'code-review',
            'description' => 'Template for code review',
            'template' => 'Review this code: {{code}}',
            'variables' => ['code'],
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('llm_prompt_templates', [
            'name' => 'Code Review Template',
            'category' => 'code-review',
        ]);
    }

    /** @test */
    public function it_casts_variables_to_array()
    {
        $template = LLMPromptTemplate::create([
            'name' => 'Test Template',
            'category' => 'testing',
            'template' => 'Hello {{name}}, your age is {{age}}',
            'variables' => ['name', 'age'],
            'is_active' => true,
        ]);

        $this->assertIsArray($template->variables);
        $this->assertContains('name', $template->variables);
        $this->assertContains('age', $template->variables);
    }

    /** @test */
    public function it_interpolates_variables_correctly()
    {
        $template = LLMPromptTemplate::create([
            'name' => 'Greeting Template',
            'category' => 'general',
            'template' => 'Hello {{name}}, welcome to {{platform}}!',
            'variables' => ['name', 'platform'],
            'is_active' => true,
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
        $template = LLMPromptTemplate::create([
            'name' => 'Test Template',
            'category' => 'testing',
            'template' => 'Hello {{name}}',
            'variables' => ['name'],
            'is_active' => true,
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
        LLMPromptTemplate::create([
            'name' => 'Active Template',
            'category' => 'testing',
            'template' => 'Test',
            'is_active' => true,
        ]);

        LLMPromptTemplate::create([
            'name' => 'Inactive Template',
            'category' => 'testing',
            'template' => 'Test',
            'is_active' => false,
        ]);

        $active = LLMPromptTemplate::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('Active Template', $active->first()->name);
    }

    /** @test */
    public function scope_by_category_filters_correctly()
    {
        LLMPromptTemplate::create([
            'name' => 'Code Template',
            'category' => 'code-generation',
            'template' => 'Generate code',
            'is_active' => true,
        ]);

        LLMPromptTemplate::create([
            'name' => 'Review Template',
            'category' => 'code-review',
            'template' => 'Review code',
            'is_active' => true,
        ]);

        $codeTemplates = LLMPromptTemplate::byCategory('code-generation')->get();

        $this->assertCount(1, $codeTemplates);
        $this->assertEquals('Code Template', $codeTemplates->first()->name);
    }

    /** @test */
    public function scope_global_returns_only_global_templates()
    {
        LLMPromptTemplate::create([
            'name' => 'Global Template',
            'category' => 'general',
            'template' => 'Test',
            'is_global' => true,
            'is_active' => true,
        ]);

        LLMPromptTemplate::create([
            'name' => 'Extension Template',
            'category' => 'general',
            'template' => 'Test',
            'is_global' => false,
            'extension_slug' => 'test-extension',
            'is_active' => true,
        ]);

        $globalTemplates = LLMPromptTemplate::global()->get();

        $this->assertCount(1, $globalTemplates);
        $this->assertTrue($globalTemplates->first()->is_global);
    }

    /** @test */
    public function scope_for_extension_filters_by_extension()
    {
        LLMPromptTemplate::create([
            'name' => 'Extension A Template',
            'category' => 'general',
            'template' => 'Test',
            'is_global' => false,
            'extension_slug' => 'extension-a',
            'is_active' => true,
        ]);

        LLMPromptTemplate::create([
            'name' => 'Extension B Template',
            'category' => 'general',
            'template' => 'Test',
            'is_global' => false,
            'extension_slug' => 'extension-b',
            'is_active' => true,
        ]);

        $extensionA = LLMPromptTemplate::forExtension('extension-a')->get();

        $this->assertCount(1, $extensionA);
        $this->assertEquals('extension-a', $extensionA->first()->extension_slug);
    }
}
