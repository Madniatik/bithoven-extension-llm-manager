<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMToolDefinition;

class LLMToolDefinitionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed default tool definitions for function calling
     */
    public function run(): void
    {
        $tools = [
            [
                'name' => 'Read File',
                'slug' => 'read-file',
                'type' => 'function_calling',
                'function_schema' => [
                    'name' => 'read_file',
                    'description' => 'Read the contents of a file from the filesystem',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'path' => [
                                'type' => 'string',
                                'description' => 'The path to the file to read',
                            ],
                        ],
                        'required' => ['path'],
                    ],
                ],
                'handler_class' => 'Bithoven\LLMManager\Services\Tools\Handlers\FileSystemHandler',
                'handler_method' => 'readFile',
                'validation_rules' => [
                    'path' => 'required|string',
                ],
                'security_policy' => [
                    'allowed_paths' => [
                        'storage/app',
                        'storage/logs',
                        'temp',
                    ],
                    'allowed_extensions' => ['txt', 'json', 'md', 'log', 'php', 'js', 'css', 'html'],
                    'max_file_size' => 1048576, // 1MB
                ],
                'is_active' => true,
                'description' => 'Read file contents with security validation',
            ],
            [
                'name' => 'Write File',
                'slug' => 'write-file',
                'type' => 'function_calling',
                'function_schema' => [
                    'name' => 'write_file',
                    'description' => 'Write content to a file in the filesystem',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'path' => [
                                'type' => 'string',
                                'description' => 'The path to the file to write',
                            ],
                            'content' => [
                                'type' => 'string',
                                'description' => 'The content to write to the file',
                            ],
                        ],
                        'required' => ['path', 'content'],
                    ],
                ],
                'handler_class' => 'Bithoven\LLMManager\Services\Tools\Handlers\FileSystemHandler',
                'handler_method' => 'writeFile',
                'validation_rules' => [
                    'path' => 'required|string',
                    'content' => 'required|string',
                ],
                'security_policy' => [
                    'allowed_paths' => [
                        'storage/app',
                        'temp',
                    ],
                    'allowed_extensions' => ['txt', 'json', 'md', 'log'],
                    'max_file_size' => 1048576, // 1MB
                ],
                'is_active' => true,
                'description' => 'Write file contents with security validation',
            ],
            [
                'name' => 'Execute Database Query',
                'slug' => 'execute-db-query',
                'type' => 'function_calling',
                'function_schema' => [
                    'name' => 'execute_db_query',
                    'description' => 'Execute a read-only database query',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'The SQL query to execute (SELECT only)',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
                'handler_class' => 'Bithoven\LLMManager\Services\Tools\Handlers\DatabaseHandler',
                'handler_method' => 'executeQuery',
                'validation_rules' => [
                    'query' => 'required|string|starts_with:SELECT',
                ],
                'security_policy' => [
                    'read_only' => true,
                    'max_results' => 100,
                ],
                'is_active' => true,
                'description' => 'Execute read-only database queries',
            ],
            [
                'name' => 'Laravel Artisan Command',
                'slug' => 'laravel-artisan',
                'type' => 'function_calling',
                'function_schema' => [
                    'name' => 'laravel_artisan',
                    'description' => 'Execute a Laravel Artisan command',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'command' => [
                                'type' => 'string',
                                'description' => 'The Artisan command to execute',
                            ],
                            'arguments' => [
                                'type' => 'array',
                                'description' => 'Arguments for the command',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['command'],
                    ],
                ],
                'handler_class' => 'Bithoven\LLMManager\Services\Tools\Handlers\LaravelHandler',
                'handler_method' => 'executeArtisan',
                'validation_rules' => [
                    'command' => 'required|string',
                    'arguments' => 'array',
                ],
                'security_policy' => [
                    'allowed_commands' => [
                        'route:list',
                        'view:cache',
                        'cache:clear',
                        'config:cache',
                    ],
                ],
                'is_active' => false, // Disabled by default for security
                'description' => 'Execute whitelisted Laravel Artisan commands',
            ],
        ];

        foreach ($tools as $tool) {
            LLMToolDefinition::updateOrCreate(
                ['slug' => $tool['slug']],
                $tool
            );
        }

        $this->command->info('✅ LLM Tool Definitions seeded');
    }
}
