<?php

namespace Bithoven\LLMManager\Console\Commands;

use Illuminate\Console\Command;
use Bithoven\LLMManager\Models\LLMMCPConnector;

class LLMMCPAddCommand extends Command
{
    protected $signature = 'mcp:add
                            {slug : Server slug}
                            {name : Server name}
                            {--protocol=stdio : Protocol (stdio/http/sse)}
                            {--path= : Server path}
                            {--port=3000 : HTTP port (if protocol is http)}
                            {--auto-start : Auto-start on boot}';

    protected $description = 'Add a new MCP server';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $name = $this->argument('name');
        $protocol = $this->option('protocol');
        $path = $this->option('path');
        $port = $this->option('port');
        $autoStart = $this->option('auto-start');

        // Check if already exists
        if (LLMMCPConnector::where('slug', $slug)->exists()) {
            $this->error("MCP server '{$slug}' already exists.");
            return self::FAILURE;
        }

        // Build configuration
        $configuration = [
            'protocol' => $protocol,
            'auto_start' => $autoStart,
        ];

        if ($path) {
            $configuration['server_path'] = $path;
        }

        if ($protocol === 'http' || $protocol === 'sse') {
            $configuration['host'] = 'localhost';
            $configuration['port'] = (int) $port;
        }

        // Create connector
        $connector = LLMMCPConnector::create([
            'slug' => $slug,
            'name' => $name,
            'protocol' => $protocol,
            'configuration' => $configuration,
            'is_active' => true,
        ]);

        $this->info("✓ MCP server '{$slug}' added successfully");
        $this->line("Protocol: {$protocol}");
        if ($path) {
            $this->line("Path: {$path}");
        }
        if ($protocol !== 'stdio') {
            $this->line("Port: {$port}");
        }
        $this->line("Auto-start: " . ($autoStart ? 'Yes' : 'No'));

        return self::SUCCESS;
    }
}
