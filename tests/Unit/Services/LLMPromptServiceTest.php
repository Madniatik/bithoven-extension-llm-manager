<?php

namespace Bithoven\LLMManager\Tests\Unit\Services;

use Bithoven\LLMManager\Models\LLMPromptTemplate;
use Bithoven\LLMManager\Services\LLMPromptService;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMPromptServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LLMPromptService $promptService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->promptService = app(LLMPromptService::class);
    }

    /** @test */
    public function it_can_get_template_by_name()
    {
        $template = LLMPromptTemplate::factory()->create([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'category' => 'testing',
            'template' => 'Hello {{name}}',
            'variables' => ['name'],
            'is_active' => true,
        ]);

        $retrieved = $this->promptService->getTemplate('Test Template');

        $this->assertEquals($template->id, $retrieved->id);
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_template()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Prompt template not found: Nonexistent');

        $this->promptService->getTemplate('Nonexistent');
    }

    /** @test */
    public function it_throws_exception_for_inactive_template()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Inactive Template',
            'slug' => 'inactive-template',
            'category' => 'testing',
            'template' => 'Test',
            'is_active' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Prompt template is not active');

        $this->promptService->getTemplate('Inactive Template');
    }

    /** @test */
    public function it_renders_template_with_variables()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Greeting',
            'slug' => 'greeting',
            'category' => 'general',
            'template' => 'Hello {{name}}, you are {{age}} years old.',
            'variables' => ['name', 'age'],
            'is_active' => true,
        ]);

        $result = $this->promptService->render('greeting', [
            'name' => 'John',
            'age' => '30',
        ]);

        $this->assertEquals('Hello John, you are 30 years old.', $result);
    }

    /** @test */
    public function it_gets_templates_by_category()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Code Template 1',
            'category' => 'code-generation',
            'template' => 'Generate code',
            'is_active' => true,
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Code Template 2',
            'category' => 'code-generation',
            'template' => 'Generate more code',
            'is_active' => true,
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Review Template',
            'category' => 'code-review',
            'template' => 'Review code',
            'is_active' => true,
        ]);

        $codeTemplates = $this->promptService->getTemplatesByCategory('code-generation');

        $this->assertCount(2, $codeTemplates);
    }

    /** @test */
    public function it_gets_global_templates()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Global Template',
            'category' => 'general',
            'template' => 'Global',
            'extension_slug' => null, // Global = null
            'is_active' => true,
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Extension Template',
            'category' => 'general',
            'template' => 'Extension specific',
            'extension_slug' => 'test-ext',
            'is_active' => true,
        ]);

        $globalTemplates = $this->promptService->getGlobalTemplates();

        $this->assertCount(1, $globalTemplates);
        $this->assertNull($globalTemplates->first()->extension_slug);
    }

    /** @test */
    public function it_gets_templates_for_extension()
    {
        LLMPromptTemplate::factory()->create([
            'name' => 'Extension A Template',
            'category' => 'general',
            'template' => 'Ext A',
            'extension_slug' => 'extension-a',
            'is_active' => true,
        ]);

        LLMPromptTemplate::factory()->create([
            'name' => 'Extension B Template',
            'category' => 'general',
            'template' => 'Ext B',
            'extension_slug' => 'extension-b',
            'is_active' => true,
        ]);

        $extensionATemplates = $this->promptService->getExtensionTemplates('extension-a');

        $this->assertCount(1, $extensionATemplates);
        $this->assertEquals('extension-a', $extensionATemplates->first()->extension_slug);
    }

    /** @test */
    public function it_validates_template_variables()
    {
        $template = LLMPromptTemplate::factory()->create([
            'name' => 'Test Template',
            'slug' => 'test-template-vars',
            'category' => 'testing',
            'template' => 'Hello {{name}}, your role is {{role}}',
            'variables' => ['name', 'role'],
            'is_active' => true,
        ]);

        $valid = $this->promptService->validateVariables($template, [
            'name' => 'John',
            'role' => 'Developer',
        ]);

        $this->assertTrue($valid);

        $invalid = $this->promptService->validateVariables($template, [
            'name' => 'John',
            // missing 'role'
        ]);

        $this->assertFalse($invalid);
    }
}
