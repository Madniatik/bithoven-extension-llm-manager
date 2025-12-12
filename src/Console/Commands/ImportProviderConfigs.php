<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\LLMConfigurationService;
use Bithoven\LLMManager\Services\ProviderRepositoryValidator;
use Bithoven\LLMManager\Models\LLMProvider;
use Illuminate\Support\Facades\DB;

/**
 * Import LLM Configurations from Provider Repository Package
 * 
 * Imports pre-configured LLM model configurations from installed
 * Composer packages (e.g., bithoven/llm-provider-openai).
 * 
 * @package Bithoven\LLMManager\Console\Commands
 * @version 0.4.0
 * @since 0.4.0
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

        // Check for archived provider (includes archived in query)
        $providerModel = LLMProvider::withArchived()->where('slug', $provider)->first();
        $isRestoring = false;

        if ($providerModel && $providerModel->isArchived()) {
            $this->warn("📦 Provider '{$provider}' was archived");
            $this->line("   Archived at: " . $providerModel->archived_at->format('Y-m-d H:i:s'));
            $this->line("   Reason: " . ($providerModel->metadata['archive_reason'] ?? 'unknown'));
            $this->newLine();
            $this->info("♻️  Restoring provider...");
            $isRestoring = true;
        }

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

        // Restore provider if archived
        if ($isRestoring && !$isDryRun) {
            $providerModel->restore();
            
            // Update metadata
            $metadata = $providerModel->metadata ?? [];
            $metadata['restore_count'] = ($metadata['restore_count'] ?? 0) + 1;
            $metadata['restored_at'] = now()->toIso8601String();
            $providerModel->update([
                'metadata' => $metadata,
                'version' => $manifest['version'] ?? null,
            ]);

            $this->info("✅ Provider '{$provider}' restored successfully");
            $this->newLine();
        } elseif (!$providerModel && !$isDryRun) {
            // Create new provider record if doesn't exist
            $providerModel = LLMProvider::create([
                'slug' => $provider,
                'name' => $manifest['display_name'] ?? ucfirst($provider),
                'package' => $manifest['package_name'] ?? "bithoven/llm-provider-{$provider}",
                'version' => $manifest['version'] ?? null,
                'api_endpoint' => $manifest['api_endpoint'] ?? null,
                'capabilities' => $manifest['capabilities'] ?? [],
                'is_active' => true,
                'is_installed' => true,
                'metadata' => [
                    'type' => $manifest['type'] ?? 'cloud',
                    'requires_api_key' => $manifest['requires_api_key'] ?? true,
                    'imported_at' => now()->toIso8601String(),
                ],
            ]);

            $this->info("✅ Provider '{$provider}' registered");
            $this->newLine();
        }

        // Import configurations
        $this->importConfigurations($configFiles, $isDryRun, $isForce, $providerModel);

        // Display summary
        $this->displaySummary($isRestoring);

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
    private function importConfigurations(array $files, bool $isDryRun, bool $isForce, ?LLMProvider $provider): void
    {
        foreach ($files as $file) {
            $this->importConfigFile($file, $isDryRun, $isForce, $provider);
        }
    }

    /**
     * Import a single configuration file
     */
    private function importConfigFile(string $filePath, bool $isDryRun, bool $isForce, ?LLMProvider $provider): void
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
            
            // Add provider_id if available
            if ($provider) {
                $config['provider_id'] = $provider->id;
            }

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
    private function displaySummary(bool $isRestoring = false): void
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
                if ($isRestoring) {
                    $this->info("♻️  Provider restored and configurations imported successfully!");
                } else {
                    $this->info("✨ Configurations imported successfully!");
                }
            }
        }
    }
}
