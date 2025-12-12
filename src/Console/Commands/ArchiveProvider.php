<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Models\LLMProvider;
use Bithoven\LLMManager\Models\LLMUsageLog;

class ArchiveProvider extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'llm:archive-provider {slug : Provider slug to archive}';

    /**
     * The console command description.
     */
    protected $description = 'Archive a provider when its package is uninstalled (preserves data)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $slug = $this->argument('slug');
        
        $provider = LLMProvider::where('slug', $slug)->first();
        
        if (!$provider) {
            $this->error("❌ Provider '{$slug}' not found");
            return Command::FAILURE;
        }

        if ($provider->isArchived()) {
            $this->warn("⚠️  Provider '{$slug}' is already archived");
            return Command::SUCCESS;
        }

        // Check dependencies
        $configCount = $provider->configurations()->count();
        $usageCount = LLMUsageLog::whereHas('configuration', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->count();

        $this->warn("⚠️  Provider '{$slug}' has:");
        $this->line("  - {$configCount} configurations");
        $this->line("  - {$usageCount} usage logs");
        $this->newLine();

        // Archive (NO delete)
        $provider->archive('package_uninstalled');

        $this->info("✅ Provider '{$slug}' archived successfully");
        $this->line('   - is_active: false');
        $this->line('   - is_installed: false');
        $this->line('   - archived_at: ' . $provider->archived_at->format('Y-m-d H:i:s'));
        $this->line('   - Configurations: disabled');
        $this->line('   - Usage logs: preserved');
        $this->line('   - Stats/Metrics: still accessible');
        $this->newLine();
        $this->comment('💡 Data preserved. Reinstall package and run `php artisan llm:import {$slug}` to restore.');

        return Command::SUCCESS;
    }
}
