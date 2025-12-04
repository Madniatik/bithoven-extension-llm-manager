<?php

namespace Bithoven\LLMManager\Services\Providers;

use Bithoven\LLMManager\Contracts\LLMProviderInterface;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Support\Facades\Http;

class OpenRouterProvider implements LLMProviderInterface
{
    public function __construct(protected LLMConfiguration $configuration)
    {
    }

    public function generate(string $prompt, array $parameters = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->configuration->api_key,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $this->configuration->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $parameters['temperature'] ?? 0.7,
            'max_tokens' => $parameters['max_tokens'] ?? 4096,
            'top_p' => $parameters['top_p'] ?? 1,
        ]);

        if (!$response->successful()) {
            throw new \Exception("OpenRouter API error: {$response->body()}");
        }

        $data = $response->json();

        return [
            'response' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => [
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                'total_tokens' => $data['usage']['total_tokens'] ?? 0,
            ],
        ];
    }

    public function embed(string|array $text): array
    {
        // OpenRouter doesn't support embeddings
        throw new \Exception('OpenRouter does not support embeddings. Use OpenAI or local provider.');
    }

    public function stream(string $prompt, array $context, array $parameters, callable $callback): array
    {
        // Build messages array with context
        $messages = [];
        
        // Add context messages if provided
        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }
        
        // Add current user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Ensure parameters are of correct types
        $temperature = (float) ($parameters['temperature'] ?? $this->configuration->default_parameters['temperature'] ?? 0.7);
        $maxTokens = (int) ($parameters['max_tokens'] ?? $this->configuration->default_parameters['max_tokens'] ?? 4096);
        $topP = (float) ($parameters['top_p'] ?? $this->configuration->default_parameters['top_p'] ?? 1);

        // OpenRouter streaming endpoint
        $endpoint = 'https://openrouter.ai/api/v1/chat/completions';

        // Prepare request payload
        $payload = [
            'model' => $this->configuration->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => $topP,
            'stream' => true,
        ];

        // Storage for final data
        $finalData = null;
        $generationId = null;

        // Initialize cURL for SSE streaming
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->configuration->api_key,
                'HTTP-Referer: ' . config('app.url'),
                'X-Title: ' . config('app.name'),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_WRITEFUNCTION => function ($curl, $data) use ($callback, &$finalData, &$generationId) {
                $lines = explode("\n", $data);
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    // Skip empty lines and comments
                    if (empty($line) || strpos($line, ':') === 0) {
                        continue;
                    }
                    
                    // Parse SSE format: "data: {...}"
                    if (strpos($line, 'data: ') === 0) {
                        $jsonData = substr($line, 6); // Remove "data: " prefix
                        
                        // Check for [DONE] marker
                        if (trim($jsonData) === '[DONE]') {
                            continue;
                        }
                        
                        try {
                            $chunk = json_decode($jsonData, true);
                            
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                continue;
                            }
                            
                            // Capture generation ID from first chunk
                            if (!$generationId && isset($chunk['id'])) {
                                $generationId = $chunk['id'];
                            }
                            
                            // Store final chunk data
                            $finalData = $chunk;
                            
                            // Extract and send content delta
                            if (isset($chunk['choices'][0]['delta']['content'])) {
                                $content = $chunk['choices'][0]['delta']['content'];
                                if (!empty($content)) {
                                    $callback($content);
                                }
                            }
                        } catch (\Exception $e) {
                            // Ignore JSON parse errors for malformed chunks
                        }
                    }
                }
                
                return strlen($data);
            },
        ]);

        curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("OpenRouter streaming error: {$error}");
        }
        
        curl_close($ch);

        // Extract usage metrics from final chunk
        // Note: OpenRouter streams don't include usage in final chunk
        // We need to fetch it via generation endpoint using the generation_id
        $usage = [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'input_tokens' => 0,
            'output_tokens' => 0,
            'native_tokens_prompt' => null,
            'native_tokens_completion' => null,
        ];

        // Fetch actual usage data from generation endpoint
        if ($generationId) {
            try {
                $generationResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->configuration->api_key,
                ])->timeout(10)->get('https://openrouter.ai/api/v1/generation', [
                    'id' => $generationId,
                ]);

                if ($generationResponse->successful()) {
                    $generationData = $generationResponse->json('data');
                    
                    if ($generationData && isset($generationData['usage'])) {
                        $usage = [
                            'prompt_tokens' => $generationData['usage']['prompt_tokens'] ?? 0,
                            'completion_tokens' => $generationData['usage']['completion_tokens'] ?? 0,
                            'total_tokens' => $generationData['usage']['total_tokens'] ?? 0,
                            'input_tokens' => $generationData['usage']['prompt_tokens'] ?? 0,
                            'output_tokens' => $generationData['usage']['completion_tokens'] ?? 0,
                            'native_tokens_prompt' => $generationData['native_tokens_prompt'] ?? null,
                            'native_tokens_completion' => $generationData['native_tokens_completion'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('OpenRouter: Failed to fetch generation data', [
                    'generation_id' => $generationId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'usage' => $usage,
            'model' => $finalData['model'] ?? $this->configuration->model,
            'finish_reason' => $finalData['choices'][0]['finish_reason'] ?? 'stop',
            'generation_id' => $generationId,
            'system_fingerprint' => $finalData['system_fingerprint'] ?? null,
            'created_at' => $finalData['created'] ?? null,
            // Raw response for debugging and analysis
            'raw_response' => $finalData,
        ];
    }

    public function supports(string $feature): bool
    {
        $capabilities = $this->configuration->capabilities ?? [];

        return match ($feature) {
            'streaming' => $capabilities['streaming'] ?? true,
            'vision' => $capabilities['vision'] ?? true,
            'function_calling' => $capabilities['function_calling'] ?? false,
            'json_mode' => $capabilities['json_mode'] ?? true,
            default => false,
        };
    }
}
