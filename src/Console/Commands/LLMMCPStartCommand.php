<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Services\MCP\LLMMCPConnectorManager;

class LLMMCPStartCommand extends Command
{
    protected $signature = 'mcp:start {server? : Server slug to start}
                            {--all : Start all auto-start servers}';

    protected $description = 'Start MCP server(s)';

    public function handle(LLMMCPConnectorManager $manager): int
    {
        if ($this->option('all')) {
            $this->info('Starting all auto-start MCP servers...');
            
            $results = $manager->startAll();
            
            foreach ($results as $slug => $result) {
                if ($result['success']) {
                    $this->info("✓ {$slug}: {$result['message']}");
                } else {
                    $this->error("✗ {$slug}: {$result['error']}");
                }
            }
            
            return self::SUCCESS;
        }

        $server = $this->argument('server');
        
        if (!$server) {
            $this->error('Please provide a server slug or use --all option');
            return self::FAILURE;
        }

        try {
            $this->info("Starting MCP server: {$server}");
            
            $result = $manager->start($server);
            
            $this->info("✓ {$result['message']}");
            $this->line("Status: {$result['status']}");
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to start server: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
