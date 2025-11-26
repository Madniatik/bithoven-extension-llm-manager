<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Bithoven\LLMManager\Models\LLMPromptTemplate;

class LLMPromptTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed essential prompt templates for common use cases
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Code Review Assistant',
                'slug' => 'code-review',
                'extension_slug' => 'llm-manager',
                'category' => 'analysis',
                'template' => 'Review this {{language}} code and provide feedback on:\n1. Code quality and best practices\n2. Potential bugs or security issues\n3. Performance improvements\n4. Readability and maintainability\n\n```{{language}}\n{{code}}\n```',
                'variables' => ['language', 'code'],
                'example_values' => [
                    'language' => 'php',
                    'code' => 'function getUserData($id) { return DB::table("users")->where("id", $id)->first(); }',
                ],
                'default_parameters' => [
                    'temperature' => 0.3,
                    'max_tokens' => 1000,
                ],
                'is_active' => true,
                'is_global' => true,
                'description' => 'Automated code review with best practices analysis',
            ],
            [
                'name' => 'Text Summarization',
                'slug' => 'text-summarization',
                'extension_slug' => 'llm-manager',
                'category' => 'summarization',
                'template' => 'Summarize the following text in {{length}} sentences:\n\n{{text}}',
                'variables' => ['text', 'length'],
                'example_values' => [
                    'text' => 'Laravel is a web application framework with expressive, elegant syntax...',
                    'length' => '2-3',
                ],
                'default_parameters' => [
                    'temperature' => 0.3,
                    'max_tokens' => 200,
                ],
                'is_active' => true,
                'is_global' => true,
                'description' => 'Generate concise summaries of long texts',
            ],
            [
                'name' => 'Documentation Generator',
                'slug' => 'documentation-generator',
                'extension_slug' => 'llm-manager',
                'category' => 'generation',
                'template' => 'Generate comprehensive documentation for this {{type}}:\n\n```{{language}}\n{{code}}\n```\n\nInclude:\n- Description\n- Parameters/Arguments\n- Return value\n- Usage examples',
                'variables' => ['type', 'language', 'code'],
                'example_values' => [
                    'type' => 'function',
                    'language' => 'php',
                    'code' => 'public function processOrder(Order $order): bool { return $order->process(); }',
                ],
                'default_parameters' => [
                    'temperature' => 0.5,
                    'max_tokens' => 800,
                ],
                'is_active' => true,
                'is_global' => true,
                'description' => 'Generate documentation for code functions and classes',
            ],
            [
                'name' => 'Bug Analysis',
                'slug' => 'bug-analysis',
                'extension_slug' => 'llm-manager',
                'category' => 'analysis',
                'template' => 'Analyze this bug report and provide:\n1. Root cause analysis\n2. Severity assessment (low/medium/high/critical)\n3. Recommended solution\n4. Prevention strategies\n\nBug Report:\n{{bug_description}}\n\nError Message:\n{{error_message}}',
                'variables' => ['bug_description', 'error_message'],
                'example_values' => [
                    'bug_description' => 'Users cannot login after recent update',
                    'error_message' => 'SQLSTATE[42S02]: Base table or view not found',
                ],
                'default_parameters' => [
                    'temperature' => 0.4,
                    'max_tokens' => 600,
                ],
                'is_active' => true,
                'is_global' => true,
                'description' => 'Automated bug analysis and solution recommendations',
            ],
            [
                'name' => 'Translation Assistant',
                'slug' => 'translation',
                'extension_slug' => 'llm-manager',
                'category' => 'translation',
                'template' => 'Translate the following text from {{source_language}} to {{target_language}}. Maintain the original tone and context.\n\nText:\n{{text}}',
                'variables' => ['source_language', 'target_language', 'text'],
                'example_values' => [
                    'source_language' => 'English',
                    'target_language' => 'Spanish',
                    'text' => 'Welcome to our application',
                ],
                'default_parameters' => [
                    'temperature' => 0.3,
                    'max_tokens' => 500,
                ],
                'is_active' => true,
                'is_global' => true,
                'description' => 'Translate text between languages',
            ],
        ];

        foreach ($templates as $template) {
            LLMPromptTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        $this->command->info('✅ Created 5 essential prompt templates (global)');
    }
}
