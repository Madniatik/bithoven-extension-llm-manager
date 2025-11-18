<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\LLMManager;

class LLMTestCommand extends Command
{
    protected $signature = 'llm:test
                            {prompt : Prompt to test}
                            {--config= : Configuration slug to use}
                            {--temperature=0.7 : Temperature parameter}';

    protected $description = 'Test LLM generation';

    public function handle(LLMManager $llm): int
    {
        $prompt = $this->argument('prompt');
        $configSlug = $this->option('config');
        $temperature = (float) $this->option('temperature');

        try {
            $this->info("Testing LLM with prompt: {$prompt}");
            $this->newLine();

            if ($configSlug) {
                $llm->config($configSlug);
                $this->line("Using configuration: {$configSlug}");
            }

            $llm->parameters(['temperature' => $temperature]);
            $this->line("Temperature: {$temperature}");
            $this->newLine();

            $this->info('Generating response...');
            $startTime = microtime(true);

            $result = $llm->generate($prompt);

            $executionTime = (int) ((microtime(true) - $startTime) * 1000);

            $this->newLine();
            $this->info('=== RESPONSE ===');
            $this->line($result['response']);
            $this->newLine();

            $this->info('=== USAGE ===');
            if (isset($result['usage'])) {
                foreach ($result['usage'] as $key => $value) {
                    $this->line("{$key}: {$value}");
                }
            }

            $this->newLine();
            $this->info("Execution time: {$executionTime}ms");
            
            if (isset($result['cost'])) {
                $this->line("Cost: $" . number_format($result['cost'], 6));
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Test failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
