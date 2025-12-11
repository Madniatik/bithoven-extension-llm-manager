<?php

namespace Bithoven\LLMManager\Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bithoven\LLMManager\Services\ProviderRepositoryValidator;
use Illuminate\Support\Facades\File;

/**
 * Provider Repository Validator Unit Tests
 * 
 * @package Bithoven\LLMManager\Tests\Unit\Services
 * @version 1.0.0
 * @since 1.0.8
 */
class ProviderRepositoryValidatorTest extends TestCase
{
    use RefreshDatabase;

    private ProviderRepositoryValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ProviderRepositoryValidator();
    }

    /** @test */
    public function it_validates_valid_configuration()
    {
        $config = [
            'version' => '1.0.0',
            'metadata' => [
                'package' => 'bithoven/llm-provider-test',
                'created_at' => '2025-12-11T00:00:00Z',
                'updated_at' => '2025-12-11T00:00:00Z',
                'author' => 'Test Author',
            ],
            'configuration' => [
                'name' => 'Test Model',
                'slug' => 'test-model',
                'provider' => 'test',
                'model_name' => 'test-model-v1',
                'description' => 'Test description',
                'api_endpoint' => 'https://api.example.com/v1',
                'default_parameters' => [
                    'max_tokens' => 4096,
                    'temperature' => 0.7,
                ],
            ],
        ];

        $errors = $this->validator->validate($config);

        $this->assertEmpty($errors, 'Valid configuration should have no validation errors');
    }

    /** @test */
    public function it_fails_validation_when_version_is_missing()
    {
        $config = [
            'metadata' => ['package' => 'test'],
            'configuration' => [],
        ];

        $errors = $this->validator->validate($config);

        $this->assertArrayHasKey('version', $errors);
    }

    /** @test */
    public function it_fails_validation_when_metadata_is_missing()
    {
        $config = [
            'version' => '1.0.0',
            'configuration' => [],
        ];

        $errors = $this->validator->validate($config);

        $this->assertArrayHasKey('metadata', $errors);
    }

    /** @test */
    public function it_validates_slug_format()
    {
        $validSlugs = ['test-model', 'gpt-4o', 'claude-3-opus'];
        $invalidSlugs = ['Test Model', 'test_model', 'test.model', 'test/model'];

        foreach ($validSlugs as $slug) {
            $config = $this->getMinimalValidConfig(['configuration.slug' => $slug]);
            $errors = $this->validator->validate($config);
            
            $this->assertArrayNotHasKey('configuration.slug', $errors, "Slug '{$slug}' should be valid");
        }

        foreach ($invalidSlugs as $slug) {
            $config = $this->getMinimalValidConfig(['configuration.slug' => $slug]);
            $errors = $this->validator->validate($config);
            
            $this->assertArrayHasKey('configuration.slug', $errors, "Slug '{$slug}' should be invalid");
        }
    }

    /** @test */
    public function it_validates_max_tokens_range()
    {
        $config = $this->getMinimalValidConfig(['configuration.default_parameters.max_tokens' => 0]);
        $errors = $this->validator->validate($config);
        $this->assertArrayHasKey('configuration.default_parameters.max_tokens', $errors);

        $config = $this->getMinimalValidConfig(['configuration.default_parameters.max_tokens' => 4096]);
        $errors = $this->validator->validate($config);
        $this->assertArrayNotHasKey('configuration.default_parameters.max_tokens', $errors);
    }

    /** @test */
    public function it_validates_temperature_range()
    {
        $config = $this->getMinimalValidConfig(['configuration.default_parameters.temperature' => -0.1]);
        $errors = $this->validator->validate($config);
        $this->assertArrayHasKey('configuration.default_parameters.temperature', $errors);

        $config = $this->getMinimalValidConfig(['configuration.default_parameters.temperature' => 2.1]);
        $errors = $this->validator->validate($config);
        $this->assertArrayHasKey('configuration.default_parameters.temperature', $errors);

        $config = $this->getMinimalValidConfig(['configuration.default_parameters.temperature' => 0.7]);
        $errors = $this->validator->validate($config);
        $this->assertArrayNotHasKey('configuration.default_parameters.temperature', $errors);
    }

    /** @test */
    public function it_validates_api_endpoint_is_url()
    {
        $config = $this->getMinimalValidConfig(['configuration.api_endpoint' => 'not-a-url']);
        $errors = $this->validator->validate($config);
        $this->assertArrayHasKey('configuration.api_endpoint', $errors);

        $config = $this->getMinimalValidConfig(['configuration.api_endpoint' => 'https://api.example.com']);
        $errors = $this->validator->validate($config);
        $this->assertArrayNotHasKey('configuration.api_endpoint', $errors);
    }

    /** @test */
    public function it_checks_schema_version_compatibility()
    {
        $compatible = $this->validator->isCompatibleVersion('1.0.0');
        $this->assertTrue($compatible);

        $incompatible = $this->validator->isCompatibleVersion('2.0.0');
        $this->assertFalse($incompatible);
    }

    /**
     * Helper: Get minimal valid configuration
     */
    private function getMinimalValidConfig(array $overrides = []): array
    {
        $config = [
            'version' => '1.0.0',
            'metadata' => [
                'package' => 'bithoven/llm-provider-test',
                'created_at' => '2025-12-11T00:00:00Z',
                'updated_at' => '2025-12-11T00:00:00Z',
                'author' => 'Test',
            ],
            'configuration' => [
                'name' => 'Test Model',
                'slug' => 'test-model',
                'provider' => 'test',
                'model_name' => 'test-v1',
                'api_endpoint' => 'https://api.example.com',
                'default_parameters' => [
                    'max_tokens' => 4096,
                    'temperature' => 0.7,
                ],
            ],
        ];

        foreach ($overrides as $key => $value) {
            data_set($config, $key, $value);
        }

        return $config;
    }
}
