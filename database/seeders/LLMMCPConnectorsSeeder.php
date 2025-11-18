<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMMCPConnector;

class LLMMCPConnectorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed the 4 bundled MCP servers
     */
    public function run(): void
    {
        $connectors = [
            [
                'name' => 'Filesystem MCP',
                'slug' => 'mcp-filesystem',
                'type' => 'bundled',
                'server_path' => 'vendor/bithoven/llm-manager/mcp-servers/filesystem/index.js',
                'protocol' => 'stdio',
                'capabilities' => [
                    'read_file',
                    'write_file',
                    'list_directory',
                    'create_directory',
                    'delete_file',
                    'move_file',
                    'search_files',
                ],
                'configuration' => [
                    'allowed_paths' => [
                        'storage/app',
                        'storage/logs',
                        'temp',
                    ],
                    'allowed_extensions' => ['txt', 'json', 'md', 'log', 'php', 'js', 'css', 'html'],
                    'max_file_size' => 1048576, // 1MB
                ],
                'is_active' => true,
                'auto_start' => true,
                'priority' => 100,
                'description' => 'Filesystem operations with security validation',
            ],
            [
                'name' => 'Database MCP',
                'slug' => 'mcp-database',
                'type' => 'bundled',
                'server_path' => 'vendor/bithoven/llm-manager/mcp-servers/database/server.py',
                'protocol' => 'stdio',
                'capabilities' => [
                    'execute_query',
                    'describe_table',
                    'list_tables',
                    'get_schema',
                ],
                'configuration' => [
                    'read_only' => true,
                    'max_results' => 100,
                    'timeout' => 30,
                ],
                'is_active' => true,
                'auto_start' => true,
                'priority' => 90,
                'description' => 'Database operations (read-only)',
            ],
            [
                'name' => 'Laravel MCP',
                'slug' => 'mcp-laravel',
                'type' => 'bundled',
                'server_path' => 'vendor/bithoven/llm-manager/mcp-servers/laravel/server.js',
                'protocol' => 'stdio',
                'capabilities' => [
                    'artisan_command',
                    'route_list',
                    'get_config',
                    'cache_clear',
                    'view_cache',
                ],
                'configuration' => [
                    'allowed_commands' => [
                        'route:list',
                        'view:cache',
                        'cache:clear',
                        'config:cache',
                    ],
                ],
                'is_active' => false, // Disabled by default for security
                'auto_start' => false,
                'priority' => 80,
                'description' => 'Laravel-specific operations',
            ],
            [
                'name' => 'Code Generation MCP',
                'slug' => 'mcp-code-generation',
                'type' => 'bundled',
                'server_path' => 'vendor/bithoven/llm-manager/mcp-servers/code-generation/server.js',
                'protocol' => 'stdio',
                'capabilities' => [
                    'generate_controller',
                    'generate_model',
                    'generate_migration',
                    'generate_component',
                    'analyze_code',
                ],
                'configuration' => [
                    'target_path' => 'app',
                    'templates_path' => 'vendor/bithoven/llm-manager/mcp-servers/code-generation/templates',
                ],
                'is_active' => false, // Disabled by default for security
                'auto_start' => false,
                'priority' => 70,
                'description' => 'Laravel code generation utilities',
            ],
        ];

        foreach ($connectors as $connector) {
            LLMMCPConnector::updateOrCreate(
                ['slug' => $connector['slug']],
                $connector
            );
        }

        $this->command->info('✅ LLM MCP Connectors seeded (4 bundled servers)');
    }
}
