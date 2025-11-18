<?php

namespace Bithoven\LLMManager\Tests\Unit\Models;

use Bithoven\LLMManager\Models\LLMToolDefinition;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMToolDefinitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_tool_definition()
    {
        $tool = LLMToolDefinition::create([
            'name' => 'calculate',
            'description' => 'Performs mathematical calculations',
            'type' => 'native',
            'implementation' => 'App\Services\CalculatorService@calculate',
            'parameters' => [
                'expression' => [
                    'type' => 'string',
                    'description' => 'Mathematical expression',
                    'required' => true,
                ],
            ],
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('llm_tool_definitions', [
            'name' => 'calculate',
            'type' => 'native',
        ]);
    }

    /** @test */
    public function it_casts_parameters_to_array()
    {
        $tool = LLMToolDefinition::create([
            'name' => 'test_tool',
            'description' => 'Test tool',
            'type' => 'native',
            'implementation' => 'TestService@method',
            'parameters' => [
                'param1' => ['type' => 'string', 'required' => true],
                'param2' => ['type' => 'integer', 'required' => false],
            ],
            'is_active' => true,
        ]);

        $this->assertIsArray($tool->parameters);
        $this->assertArrayHasKey('param1', $tool->parameters);
    }

    /** @test */
    public function scope_active_returns_only_active_tools()
    {
        LLMToolDefinition::create([
            'name' => 'active_tool',
            'description' => 'Active',
            'type' => 'native',
            'implementation' => 'Service@method',
            'is_active' => true,
        ]);

        LLMToolDefinition::create([
            'name' => 'inactive_tool',
            'description' => 'Inactive',
            'type' => 'native',
            'implementation' => 'Service@method',
            'is_active' => false,
        ]);

        $activeTools = LLMToolDefinition::active()->get();

        $this->assertCount(1, $activeTools);
        $this->assertEquals('active_tool', $activeTools->first()->name);
    }

    /** @test */
    public function scope_by_type_filters_correctly()
    {
        LLMToolDefinition::create([
            'name' => 'native_tool',
            'description' => 'Native',
            'type' => 'native',
            'implementation' => 'Service@method',
            'is_active' => true,
        ]);

        LLMToolDefinition::create([
            'name' => 'mcp_tool',
            'description' => 'MCP',
            'type' => 'mcp',
            'implementation' => 'mcp://server/tool',
            'is_active' => true,
        ]);

        $nativeTools = LLMToolDefinition::byType('native')->get();

        $this->assertCount(1, $nativeTools);
        $this->assertEquals('native', $nativeTools->first()->type);
    }

    /** @test */
    public function scope_for_extension_filters_by_extension()
    {
        LLMToolDefinition::create([
            'name' => 'ext_a_tool',
            'description' => 'Extension A',
            'type' => 'native',
            'implementation' => 'Service@method',
            'extension_slug' => 'extension-a',
            'is_active' => true,
        ]);

        LLMToolDefinition::create([
            'name' => 'ext_b_tool',
            'description' => 'Extension B',
            'type' => 'native',
            'implementation' => 'Service@method',
            'extension_slug' => 'extension-b',
            'is_active' => true,
        ]);

        $extATools = LLMToolDefinition::forExtension('extension-a')->get();

        $this->assertCount(1, $extATools);
        $this->assertEquals('extension-a', $extATools->first()->extension_slug);
    }

    /** @test */
    public function it_validates_required_parameters()
    {
        $tool = LLMToolDefinition::create([
            'name' => 'test_tool',
            'description' => 'Test',
            'type' => 'native',
            'implementation' => 'Service@method',
            'parameters' => [
                'required_param' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'optional_param' => [
                    'type' => 'string',
                    'required' => false,
                ],
            ],
            'is_active' => true,
        ]);

        // Valid - has required param
        $valid = $tool->validateParameters([
            'required_param' => 'value',
        ]);
        $this->assertTrue($valid);

        // Invalid - missing required param
        $invalid = $tool->validateParameters([
            'optional_param' => 'value',
        ]);
        $this->assertFalse($invalid);
    }

    /** @test */
    public function it_formats_for_function_calling()
    {
        $tool = LLMToolDefinition::create([
            'name' => 'get_weather',
            'description' => 'Get current weather',
            'type' => 'native',
            'implementation' => 'WeatherService@getCurrent',
            'parameters' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'City name',
                    'required' => true,
                ],
            ],
            'is_active' => true,
        ]);

        $formatted = $tool->toFunctionCallingFormat();

        $this->assertEquals('get_weather', $formatted['name']);
        $this->assertEquals('Get current weather', $formatted['description']);
        $this->assertArrayHasKey('parameters', $formatted);
        $this->assertEquals('object', $formatted['parameters']['type']);
    }
}
