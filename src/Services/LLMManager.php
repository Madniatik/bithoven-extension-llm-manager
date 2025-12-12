<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\App;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Bithoven\LLMManager\Services\Providers\OllamaProvider;
use Bithoven\LLMManager\Services\Providers\OpenAIProvider;
use Bithoven\LLMManager\Services\Providers\AnthropicProvider;
use Bithoven\LLMManager\Services\Providers\OpenRouterProvider;
use Bithoven\LLMManager\Services\Providers\CustomProvider;
use Bithoven\LLMManager\Contracts\LLMProviderInterface;

class LLMManager
{
    protected ?LLMProviderConfiguration $configuration = null;
    protected array $parameters = [];
    protected ?string $extensionSlug = null;
    protected ?string $context = null;

    public function __construct(protected $app)
    {
        // Set default configuration
        $defaultConfig = LLMProviderConfiguration::default()->first();
        if ($defaultConfig) {
            $this->configuration = $defaultConfig;
        }
    }

    /**
     * Set configuration by ID or slug
     * 
     * @param int|string $identifier Configuration ID (int) or slug (string)
     */
    public function config(int|string $identifier): self
    {
        // If identifier is numeric, treat as ID (preferred for immutability)
        if (is_int($identifier)) {
            $config = LLMProviderConfiguration::where('id', $identifier)
                ->active()
                ->firstOrFail();
        } else {
            // Otherwise treat as slug (for backward compatibility)
            $config = LLMProviderConfiguration::where('slug', $identifier)
                ->active()
                ->firstOrFail();
        }

        $this->configuration = $config;

        return $this;
    }

    /**
     * Set custom parameters
     */
    public function parameters(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Set extension context
     */
    public function extension(string $extensionSlug): self
    {
        $this->extensionSlug = $extensionSlug;

        return $this;
    }

    /**
     * Set execution context
     */
    public function context(string $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Generate text completion
     */
    public function generate(string $prompt, array $parameters = []): array
    {
        if (!$this->configuration) {
            throw new \Exception('No LLM configuration set');
        }

        $executor = App::make(LLMExecutor::class);
        $executor->setConfiguration($this->configuration);
        $executor->setExtensionSlug($this->extensionSlug);
        $executor->setContext($this->context);
        $executor->setCustomParameters(array_merge($this->parameters, $parameters));

        return $executor->execute($prompt);
    }

    /**
     * Generate embeddings
     */
    public function embed(string|array $text): array
    {
        if (!$this->configuration) {
            throw new \Exception('No LLM configuration set');
        }

        $provider = $this->getProvider();

        return $provider->embed($text);
    }

    /**
     * Chat in a conversation session
     */
    public function chat(string $sessionId, string $message): array
    {
        $conversationManager = App::make(Conversations\LLMConversationManager::class);

        return $conversationManager->sendMessage($sessionId, $message, $this->configuration);
    }

    /**
     * Create or get conversation session
     */
    public function conversation(?string $sessionId = null): string
    {
        $conversationManager = App::make(Conversations\LLMConversationManager::class);

        if ($sessionId) {
            return $sessionId;
        }

        $session = $conversationManager->createSession(
            $this->configuration,
            $this->extensionSlug,
            auth()->id()
        );

        return $session->session_id;
    }

    /**
     * Use prompt template
     */
    public function template(string $slug, array $variables): array
    {
        $promptService = App::make(LLMPromptService::class);

        $rendered = $promptService->render($slug, $variables);

        return $this->generate($rendered);
    }

    /**
     * Execute workflow
     */
    public function workflow(string $slug, array $input): array
    {
        $workflowEngine = App::make(Workflows\LLMWorkflowEngine::class);

        return $workflowEngine->execute($slug, $input);
    }

    /**
     * RAG search and generate
     */
    public function rag(string $query, string $extensionSlug = null): array
    {
        $ragService = App::make(RAG\LLMRAGService::class);

        $documents = $ragService->search($query, $extensionSlug ?? $this->extensionSlug);

        $context = implode("\n\n", array_column($documents, 'content'));
        $prompt = "Based on the following documentation:\n\n{$context}\n\nAnswer: {$query}";

        return $this->generate($prompt);
    }

    /**
     * Execute tool
     */
    public function tool(string $slug, array $parameters): array
    {
        $toolService = App::make(Tools\LLMToolService::class);

        return $toolService->execute($slug, $parameters);
    }

    /**
     * Record custom metric
     */
    public function recordMetric(int $usageLogId, string $key, mixed $value, string $type = 'string'): void
    {
        $metricsService = App::make(LLMMetricsService::class);

        $metricsService->record($usageLogId, $this->extensionSlug, $key, $value, $type);
    }

    /**
     * Get provider instance
     */
    public function getProvider(): LLMProviderInterface
    {
        if (!$this->configuration) {
            throw new \Exception('No LLM configuration set. Call config() first.');
        }
        
        return match ($this->configuration->provider->slug) {
            'ollama' => new OllamaProvider($this->configuration),
            'openai' => new OpenAIProvider($this->configuration),
            'anthropic' => new AnthropicProvider($this->configuration),
            'openrouter' => new OpenRouterProvider($this->configuration),
            'custom' => new CustomProvider($this->configuration),
            default => throw new \Exception("Unsupported provider: {$this->configuration->provider->slug}"),
        };
    }

    /**
     * Get current configuration or configuration by ID
     */
    public function getConfiguration(?int $id = null): ?LLMProviderConfiguration
    {
        // If no ID provided, return current configuration
        if ($id === null) {
            // Try default from config if no configuration set
            if (!$this->configuration) {
                $defaultId = config('llm-manager.default_configuration_id');
                if ($defaultId) {
                    $this->configuration = LLMProviderConfiguration::find($defaultId);
                }
            }
            return $this->configuration;
        }
        
        // Check cache first if enabled
        if (config('llm-manager.cache.enabled', false)) {
            $cacheKey = "llm_config_{$id}";
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }
        
        // Load configuration by ID
        $config = LLMProviderConfiguration::find($id);
        
        if (!$config) {
            throw new \RuntimeException('LLM configuration not found');
        }
        
        if (!$config->is_active) {
            throw new \RuntimeException('LLM configuration is not active');
        }
        
        // Cache if enabled
        if (config('llm-manager.cache.enabled', false)) {
            $ttl = config('llm-manager.cache.ttl', 3600);
            \Illuminate\Support\Facades\Cache::put("llm_config_{$id}", $config, $ttl);
        }
        
        return $config;
    }

    /**
     * Get all active configurations
     */
    public function activeConfigurations(): \Illuminate\Database\Eloquent\Collection
    {
        return LLMProviderConfiguration::active()->get();
    }

    /**
     * Get configurations by provider
     */
    public function configurationsByProvider(string $provider): \Illuminate\Database\Eloquent\Collection
    {
        return LLMProviderConfiguration::forProvider($provider)->active()->get();
    }

    /**
     * Get provider instance (public for testing)
     */
    public function provider($configOrNull = null): LLMProviderInterface
    {
        $config = $configOrNull ?? $this->configuration;
        
        if (!$config) {
            throw new \RuntimeException('No LLM configuration set');
        }
        
        // Load provider relationship if not already loaded
        if (!$config->relationLoaded('provider')) {
            $config->load('provider');
        }
        
        $providerSlug = $config->provider->slug;
        
        return match ($providerSlug) {
            'ollama' => new OllamaProvider($config),
            'openai' => new OpenAIProvider($config),
            'anthropic' => new AnthropicProvider($config),
            'openrouter' => new OpenRouterProvider($config),
            'custom' => new CustomProvider($config),
            default => throw new \RuntimeException("Unsupported LLM provider: {$providerSlug}"),
        };
    }
}
