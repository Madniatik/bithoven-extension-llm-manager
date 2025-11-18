<?php

namespace Bithoven\LLMManager\Tests\Feature\Http\Controllers;

use App\Models\User;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LLMConfigurationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable middleware for testing
        $this->withoutMiddleware();
        
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    /** @test */
    public function it_displays_configurations_index_page()
    {
        $response = $this->get(route('admin.llm.configurations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.configurations.index');
    }

    /** @test */
    public function it_displays_create_configuration_form()
    {
        $response = $this->get(route('admin.llm.configurations.create'));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.configurations.create');
        $response->assertSee('Create Configuration');
    }

    /** @test */
    public function it_can_create_a_configuration()
    {
        $data = [
            'name' => 'Test OpenAI Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => 'sk-test-key-123',
            'parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ],
            'is_active' => true,
        ];

        $response = $this->post(route('admin.llm.configurations.store'), $data);

        $response->assertRedirect(route('admin.llm.configurations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('llm_manager_configurations', [
            'name' => 'Test OpenAI Config',
            'provider' => 'openai',
            'model' => 'gpt-4',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->post(route('admin.llm.configurations.store'), []);

        $response->assertSessionHasErrors(['name', 'provider', 'model']);
    }

    /** @test */
    public function it_displays_configuration_details()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'controller-show-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => encrypt('sk-test'),
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.llm.configurations.show', $config));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.configurations.show');
        $response->assertSee('Test Config');
    }

    /** @test */
    public function it_displays_edit_configuration_form()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'controller-edit-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => encrypt('sk-test'),
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.llm.configurations.edit', $config));

        $response->assertStatus(200);
        $response->assertViewIs('llm-manager::admin.configurations.edit');
        $response->assertSee('Test Config');
    }

    /** @test */
    public function it_can_update_a_configuration()
    {
        $config = LLMConfiguration::create([
            'name' => 'Original Name',
            'slug' => 'controller-update-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => encrypt('sk-test'),
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'provider' => 'openai',
            'model' => 'gpt-4-turbo',
            'api_key' => 'sk-updated-key',
            'parameters' => [
                'temperature' => 0.9,
            ],
            'is_active' => false,
        ];

        $response = $this->put(route('admin.llm.configurations.update', $config), $updateData);

        $response->assertRedirect(route('admin.llm.configurations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('llm_manager_configurations', [
            'id' => $config->id,
            'name' => 'Updated Name',
            'model' => 'gpt-4-turbo',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_can_delete_a_configuration()
    {
        $config = LLMConfiguration::create([
            'name' => 'To Delete',
            'slug' => 'controller-delete-config',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => encrypt('sk-test'),
            'is_active' => true,
        ]);

        $response = $this->delete(route('admin.llm.configurations.destroy', $config));

        $response->assertRedirect(route('admin.llm.configurations.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('llm_configurations', ['id' => $config->id]);
    }

    /** @test */
    public function it_can_test_configuration_connection()
    {
        $config = LLMConfiguration::create([
            'name' => 'Test Config',
            'slug' => 'controller-test-connection',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'api_key' => encrypt('sk-test'),
            'endpoint_url' => 'https://api.openai.com/v1/chat/completions',
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.llm.configurations.test'), [
            'endpoint' => $config->endpoint_url,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success']);
    }

    /** @test */
    public function unauthorized_users_cannot_access_configurations()
    {
        auth()->logout();

        $response = $this->get(route('admin.llm.configurations.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_displays_configurations_in_index()
    {
        LLMConfiguration::create([
            'name' => 'Config 1',
            'slug' => 'controller-index-config-1',
            'provider' => 'openai',
            'model' => 'gpt-4',
            'is_active' => true,
        ]);

        LLMConfiguration::create([
            'name' => 'Config 2',
            'slug' => 'controller-index-config-2',
            'provider' => 'anthropic',
            'model' => 'claude-3',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.llm.configurations.index'));

        $response->assertSee('Config 1');
        $response->assertSee('Config 2');
    }
}
