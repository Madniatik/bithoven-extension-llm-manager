<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Models\LLMMCPConnector;
use Bithoven\LLMManager\Services\MCP\LLMMCPConnectorManager;

class LLMMCPListCommand extends Command
{
    protected $signature = 'mcp:list {--active : Show only running servers}';

    protected $description = 'List MCP servers';

    public function handle(LLMMCPConnectorManager $manager): int
    {
        $query = LLMMCPConnector::query();
        
        if ($this->option('active')) {
            $query->running();
        }
        
        $connectors = $query->get();

        if ($connectors->isEmpty()) {
            $this->info('No MCP servers found.');
            return self::SUCCESS;
        }

        $headers = ['Slug', 'Name', 'Protocol', 'Status', 'Tools'];
        $rows = [];

        foreach ($connectors as $connector) {
            $tools = [];
            
            if ($connector->isRunning()) {
                try {
                    $tools = $manager->listTools($connector->slug);
                } catch (\Exception $e) {
                    $tools = ['Error loading'];
                }
            }
            
            $rows[] = [
                $connector->slug,
                $connector->name,
                $connector->protocol,
                $connector->status,
                count($tools) . ' tools',
            ];
        }

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
