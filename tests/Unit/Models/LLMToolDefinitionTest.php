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
        $tool = LLMToolDefinition::factory()->create([
            'name' => 'calculate',
            'description' => 'Performs mathematical calculations',
        ]);

        $this->assertDatabaseHas('llm_manager_tool_definitions', [
            'name' => 'calculate',
        ]);
    }

    /** @test */
    public function it_casts_parameters_to_array()
    {
        $tool = LLMToolDefinition::factory()->create([
            'name' => 'test_tool',
            'function_schema' => [
                'param1' => ['type' => 'string', 'required' => true],
                'param2' => ['type' => 'integer', 'required' => false],
            ],
        ]);

        $this->assertIsArray($tool->function_schema);
        $this->assertArrayHasKey('param1', $tool->function_schema);
    }

    /** @test */
    public function scope_active_returns_only_active_tools()
    {
        LLMToolDefinition::factory()->create([
            'name' => 'active_tool',
            'is_active' => true,
        ]);

        LLMToolDefinition::factory()->inactive()->create([
            'name' => 'inactive_tool',
        ]);

        $activeTools = LLMToolDefinition::active()->get();

        $this->assertCount(1, $activeTools);
        $this->assertEquals('active_tool', $activeTools->first()->name);
    }

    /** @test */
    public function scope_by_type_filters_correctly()
    {
        LLMToolDefinition::factory()->functionCalling()->create([
            'name' => 'function_calling_tool',
        ]);

        LLMToolDefinition::factory()->mcp()->create([
            'name' => 'mcp_tool',
        ]);

        $functionCallingTools = LLMToolDefinition::byType('function_calling')->get();

        $this->assertCount(1, $functionCallingTools);
        $this->assertEquals('function_calling', $functionCallingTools->first()->type);
    }

    /** @test */
    public function it_validates_required_parameters()
    {
        $tool = LLMToolDefinition::factory()->create([
            'name' => 'test_tool',
            'function_schema' => [
                'required_param' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'optional_param' => [
                    'type' => 'string',
                    'required' => false,
                ],
            ],
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
        $tool = LLMToolDefinition::factory()->create([
            'name' => 'get_weather',
            'description' => 'Get current weather',
            'function_schema' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'City name',
                    'required' => true,
                ],
            ],
        ]);

        $formatted = $tool->toFunctionCallingFormat();

        $this->assertEquals('get_weather', $formatted['name']);
        $this->assertEquals('Get current weather', $formatted['description']);
        $this->assertArrayHasKey('parameters', $formatted);
        $this->assertEquals('object', $formatted['parameters']['type']);
    }
}
