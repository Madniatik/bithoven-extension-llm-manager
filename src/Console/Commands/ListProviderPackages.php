<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * List Available Provider Repository Packages
 * 
 * Displays available and installed provider configuration packages
 * from the bithoven ecosystem.
 * 
 * @package Bithoven\LLMManager\Console\Commands
 * @version 1.0.0
 * @since 1.0.8
 */
class ListProviderPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llm:packages 
                            {--installed : Show only installed packages}
                            {--available : Show only available packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List available provider repository packages';

    /**
     * Available packages catalog
     * 
     * @var array
     */
    private array $packagesCatalog = [
        'bithoven/llm-provider-openai' => [
            'name' => 'OpenAI',
            'description' => 'Official OpenAI models (GPT-4, GPT-3.5, etc.)',
            'configs' => 10,
            'status' => 'stable',
            'repository' => 'https://github.com/bithoven/llm-provider-openai',
        ],
        'bithoven/llm-provider-anthropic' => [
            'name' => 'Anthropic Claude',
            'description' => 'Claude models (Claude 3 Opus, Sonnet, Haiku)',
            'configs' => 6,
            'status' => 'stable',
            'repository' => 'https://github.com/bithoven/llm-provider-anthropic',
        ],
        'bithoven/llm-provider-ollama' => [
            'name' => 'Ollama (Local)',
            'description' => 'Local models via Ollama (Llama, Mistral, etc.)',
            'configs' => 15,
            'status' => 'stable',
            'repository' => 'https://github.com/bithoven/llm-provider-ollama',
        ],
        'bithoven/llm-provider-openrouter' => [
            'name' => 'OpenRouter',
            'description' => 'Unified API for multiple providers',
            'configs' => 8,
            'status' => 'stable',
            'repository' => 'https://github.com/bithoven/llm-provider-openrouter',
        ],
        'bithoven/llm-provider-google' => [
            'name' => 'Google AI',
            'description' => 'Gemini and PaLM models',
            'configs' => 5,
            'status' => 'beta',
            'repository' => 'https://github.com/bithoven/llm-provider-google',
        ],
        'bithoven/llm-provider-cohere' => [
            'name' => 'Cohere',
            'description' => 'Cohere command models',
            'configs' => 4,
            'status' => 'beta',
            'repository' => 'https://github.com/bithoven/llm-provider-cohere',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $showInstalled = $this->option('installed');
        $showAvailable = $this->option('available');

        // Header
        $this->info("📦 Provider Configuration Packages");
        $this->newLine();

        // Get packages to display
        $packages = $this->filterPackages($showInstalled, $showAvailable);

        if (empty($packages)) {
            $this->warn("No packages found matching your criteria.");
            return Command::SUCCESS;
        }

        // Display packages
        $this->displayPackages($packages);

        // Footer with usage instructions
        $this->displayUsageInstructions();

        return Command::SUCCESS;
    }

    /**
     * Filter packages based on options
     */
    private function filterPackages(bool $showInstalled, bool $showAvailable): array
    {
        $filtered = [];

        foreach ($this->packagesCatalog as $packageName => $info) {
            $isInstalled = $this->isPackageInstalled($packageName);

            // Apply filters
            if ($showInstalled && !$isInstalled) {
                continue;
            }

            if ($showAvailable && $isInstalled) {
                continue;
            }

            $filtered[$packageName] = array_merge($info, [
                'installed' => $isInstalled,
            ]);
        }

        return $filtered;
    }

    /**
     * Display packages in formatted output
     */
    private function displayPackages(array $packages): void
    {
        foreach ($packages as $packageName => $info) {
            $this->displayPackage($packageName, $info);
            $this->newLine();
        }
    }

    /**
     * Display a single package
     */
    private function displayPackage(string $packageName, array $info): void
    {
        // Status icon
        $statusIcon = $info['installed'] ? '✅' : '📦';
        $statusText = $info['installed'] ? 'Installed' : 'Available';

        // Package status badge
        $statusBadge = match($info['status']) {
            'stable' => '🟢 Stable',
            'beta' => '🟡 Beta',
            'alpha' => '🟠 Alpha',
            default => '⚪ Unknown',
        };

        // Header
        $this->line("{$statusIcon} <fg=cyan;options=bold>{$info['name']}</> ({$statusBadge})");
        
        // Package name
        $this->line("   Package: <fg=yellow>{$packageName}</>");
        
        // Description
        $this->line("   Description: {$info['description']}");
        
        // Configurations count
        $this->line("   Configurations: {$info['configs']}");

        // Installation instructions or import command
        if ($info['installed']) {
            $provider = $this->extractProviderName($packageName);
            $this->line("   Import: <fg=green>php artisan llm:import {$provider}</>");
        } else {
            $this->line("   Install: <fg=green>composer require {$packageName}</>");
        }

        // Repository link
        if (isset($info['repository'])) {
            $this->line("   Repository: {$info['repository']}");
        }
    }

    /**
     * Display usage instructions
     */
    private function displayUsageInstructions(): void
    {
        $this->newLine();
        $this->info("💡 Usage:");
        $this->line("   1. Install package: <fg=green>composer require bithoven/llm-provider-openai</>");
        $this->line("   2. Import configs: <fg=green>php artisan llm:import openai</>");
        $this->line("   3. Use in your app!");
        $this->newLine();
        $this->info("📚 Options:");
        $this->line("   <fg=green>--installed</>   Show only installed packages");
        $this->line("   <fg=green>--available</>   Show only available packages");
    }

    /**
     * Check if a package is installed
     */
    private function isPackageInstalled(string $packageName): bool
    {
        $vendorPath = base_path('vendor/' . $packageName);
        return is_dir($vendorPath);
    }

    /**
     * Extract provider name from package name
     * 
     * bithoven/llm-provider-openai -> openai
     */
    private function extractProviderName(string $packageName): string
    {
        $parts = explode('/', $packageName);
        $name = end($parts);
        return str_replace('llm-provider-', '', $name);
    }
}
