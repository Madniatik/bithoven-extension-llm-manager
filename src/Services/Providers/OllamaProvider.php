<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements LLMProviderInterface
{
    public function __construct(protected LLMConfiguration $configuration)
    {
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        $response = Http::timeout(120)
            ->post($this->configuration->api_endpoint, [
                'model' => $this->configuration->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => $parameters['temperature'] ?? 0.7,
                    'top_p' => $parameters['top_p'] ?? 0.9,
                    'num_predict' => $parameters['max_tokens'] ?? 2000,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("Ollama API error: {$response->body()}");
        }

        $data = $response->json();

        return [
            'response' => $data['response'] ?? '',
            'usage' => [
                'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                'completion_tokens' => $data['eval_count'] ?? 0,
                'total_tokens' => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
            ],
        ];
    }

    public function embed(string|array $text): array
    {
        $endpoint = str_replace('/api/generate', '/api/embeddings', $this->configuration->api_endpoint);

        $response = Http::post($endpoint, [
            'model' => $this->configuration->model,
            'prompt' => is_array($text) ? $text[0] : $text,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Ollama embeddings error: {$response->body()}");
        }

        return $response->json('embedding', []);
    }

    public function stream(string $prompt, array $parameters, callable $callback): void
    {
        $response = Http::timeout(120)
            ->withOptions(['stream' => true])
            ->post($this->configuration->api_endpoint, [
                'model' => $this->configuration->model,
                'prompt' => $prompt,
                'stream' => true,
                'options' => [
                    'temperature' => $parameters['temperature'] ?? 0.7,
                    'num_predict' => $parameters['max_tokens'] ?? 2000,
                ],
            ]);

        $response->onBody(function ($chunk) use ($callback) {
            $data = json_decode($chunk, true);
            if (isset($data['response'])) {
                $callback($data['response']);
            }
        });
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? true,
            'vision' => $capabilities['vision'] ?? false,
            'function_calling' => $capabilities['function_calling'] ?? false,
            'json_mode' => $capabilities['json_mode'] ?? true,
            default => false,
        };
    }
}
