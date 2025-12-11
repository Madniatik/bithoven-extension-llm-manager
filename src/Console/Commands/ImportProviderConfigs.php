<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Services\ProviderRepositoryValidator;
use Illuminate\Support\Facades\DB;

/**
 * Import LLM Configurations from Provider Repository Package
 * 
 * Imports pre-configured LLM model configurations from installed
 * Composer packages (e.g., bithoven/llm-provider-openai).
 * 
 * @package Bithoven\LLMManager\Console\Commands
 * @version 1.0.0
 * @since 1.0.8
 */
class ImportProviderConfigs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llm:import 
                            {provider : Provider name (openai, anthropic, ollama, etc.)}
                            {--force : Overwrite existing configurations}
                            {--dry-run : Show what would be imported without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import LLM configurations from provider repository package';

    /**
     * Import statistics
     */
    private int $imported = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private int $updated = 0;

    /**
     * Create a new command instance.
     */
    public function __construct(
        private LLMConfigurationService $configService,
        private ProviderRepositoryValidator $validator
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $provider = $this->argument('provider');
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info("🔍 Importing configurations for provider: {$provider}");
        $this->newLine();

        // Determine package path
        $packagePath = $this->getPackagePath($provider);

        // Check if package exists
        if (!is_dir($packagePath)) {
            $this->error("❌ Provider package not found: bithoven/llm-provider-{$provider}");
            $this->newLine();
            $this->info("💡 Install with:");
            $this->line("   composer require bithoven/llm-provider-{$provider}");
            return Command::FAILURE;
        }

        // Validate package structure
        if (!$this->validator->validatePackage($packagePath)) {
            $this->error("❌ Invalid package structure");
            $this->line("   Expected: configs/manifest.json and config files");
            return Command::FAILURE;
        }

        // Get manifest
        $manifest = $this->validator->getManifest($packagePath);
        if (!$manifest) {
            $this->error("❌ Could not read package manifest");
            return Command::FAILURE;
        }

        $this->displayManifestInfo($manifest);

        // Get config files
        $configFiles = $this->getConfigFiles($packagePath);

        if (empty($configFiles)) {
            $this->warn("⚠️  No configuration files found in package");
            return Command::FAILURE;
        }

        $this->info("📦 Found " . count($configFiles) . " configuration(s) to import");
        $this->newLine();

        // Show dry-run notice
        if ($isDryRun) {
            $this->warn("🔍 DRY RUN MODE - No changes will be saved");
            $this->newLine();
        }

        // Import configurations
        $this->importConfigurations($configFiles, $isDryRun, $isForce);

        // Display summary
        $this->displaySummary();

        return Command::SUCCESS;
    }

    /**
     * Get package path for provider
     */
    private function getPackagePath(string $provider): string
    {
        return base_path("vendor/bithoven/llm-provider-{$provider}");
    }

    /**
     * Get configuration files from package
     * 
     * @return array Array of file paths
     */
    private function getConfigFiles(string $packagePath): array
    {
        $configsPath = $packagePath . '/configs';
        $files = glob($configsPath . '/*.json');

        // Filter out manifest.json
        return array_filter($files, fn($file) => !str_ends_with($file, 'manifest.json'));
    }

    /**
     * Display package manifest information
     */
    private function displayManifestInfo(array $manifest): void
    {
        $this->line("📋 Package: {$manifest['package_name']}");
        $this->line("   Version: {$manifest['version']}");
        
        if (isset($manifest['description'])) {
            $this->line("   Description: {$manifest['description']}");
        }
        
        if (isset($manifest['author'])) {
            $this->line("   Author: {$manifest['author']}");
        }

        $this->newLine();
    }

    /**
     * Import configurations from files
     */
    private function importConfigurations(array $files, bool $isDryRun, bool $isForce): void
    {
        foreach ($files as $file) {
            $this->importConfigFile($file, $isDryRun, $isForce);
        }
    }

    /**
     * Import a single configuration file
     */
    private function importConfigFile(string $filePath, bool $isDryRun, bool $isForce): void
    {
        $filename = basename($filePath);

        try {
            // Validate file
            $result = $this->validator->validateFile($filePath);

            if (!$result['valid']) {
                $this->displayValidationErrors($filename, $result['errors']);
                $this->errors++;
                return;
            }

            $data = $result['data'];
            $config = $data['configuration'];

            // Check if configuration already exists
            $existing = $this->configService->findBySlug($config['slug']);

            if ($existing && !$isForce) {
                $this->warn("  ⏭️  {$filename}: Already exists '{$config['name']}' (use --force to overwrite)");
                $this->skipped++;
                return;
            }

            // Dry run mode
            if ($isDryRun) {
                if ($existing) {
                    $this->info("  🔄 {$filename}: Would update '{$config['name']}'");
                } else {
                    $this->info("  ✅ {$filename}: Would import '{$config['name']}'");
                }
                $this->imported++;
                return;
            }

            // Import or update configuration
            DB::beginTransaction();

            try {
                if ($existing) {
                    $this->configService->update($existing, $config);
                    $this->info("  🔄 {$filename}: Updated '{$config['name']}'");
                    $this->updated++;
                } else {
                    $this->configService->create($config);
                    $this->info("  ✅ {$filename}: Imported '{$config['name']}'");
                    $this->imported++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            $this->error("  ❌ {$filename}: {$e->getMessage()}");
            $this->errors++;
        }
    }

    /**
     * Display validation errors
     */
    private function displayValidationErrors(string $filename, array $errors): void
    {
        $this->error("  ❌ {$filename}: Validation failed");

        foreach ($errors as $field => $messages) {
            if (is_array($messages)) {
                foreach ($messages as $message) {
                    $this->line("     - {$field}: {$message}");
                }
            } else {
                $this->line("     - {$field}: {$messages}");
            }
        }
    }

    /**
     * Display import summary
     */
    private function displaySummary(): void
    {
        $this->newLine();
        
        if ($this->option('dry-run')) {
            $this->info("🔍 Dry Run Summary:");
        } else {
            $this->info("📊 Import Summary:");
        }

        $this->line("  ✅ Imported: {$this->imported}");
        
        if ($this->updated > 0) {
            $this->line("  🔄 Updated: {$this->updated}");
        }
        
        if ($this->skipped > 0) {
            $this->line("  ⏭️  Skipped: {$this->skipped}");
        }
        
        if ($this->errors > 0) {
            $this->line("  ❌ Errors: {$this->errors}");
        }

        $this->newLine();

        // Success message
        if ($this->errors === 0 && ($this->imported > 0 || $this->updated > 0)) {
            if ($this->option('dry-run')) {
                $this->info("✨ Validation successful! Remove --dry-run to import.");
            } else {
                $this->info("✨ Configurations imported successfully!");
            }
        }
    }
}
