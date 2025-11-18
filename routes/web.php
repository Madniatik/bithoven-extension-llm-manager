<?php

use Illuminate\Support\Facades\Route;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMConfigurationController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMUsageStatsController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMPromptTemplateController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMConversationController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMKnowledgeBaseController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMToolDefinitionController;

/*
|--------------------------------------------------------------------------
| LLM Manager Web Routes
|--------------------------------------------------------------------------
|
| Admin routes for LLM Manager extension
|
*/

Route::prefix('admin/llm')
    ->middleware(['web', 'auth', 'llm.admin'])
    ->name('admin.llm.')
    ->group(function () {
        
        // Dashboard
        Route::get('/', [LLMUsageStatsController::class, 'dashboard'])->name('dashboard');
        
        // Configurations
        Route::resource('configurations', LLMConfigurationController::class);
        Route::post('configurations/test', [LLMConfigurationController::class, 'testConnection'])->name('configurations.test');
        Route::post('configurations/{configuration}/toggle', [LLMConfigurationController::class, 'toggleActive'])->name('configurations.toggle');
        
        // Usage Statistics
        Route::get('statistics', [LLMUsageStatsController::class, 'index'])->name('statistics.index');
        Route::get('statistics/export', [LLMUsageStatsController::class, 'export'])->name('statistics.export');
        
        // Prompt Templates
        Route::resource('prompts', LLMPromptTemplateController::class)->parameters([
            'prompts' => 'template'
        ]);
        
        // Conversations
        Route::get('conversations', [LLMConversationController::class, 'index'])->name('conversations.index');
        Route::get('conversations/{sessionId}', [LLMConversationController::class, 'show'])->name('conversations.show');
        Route::delete('conversations/{sessionId}', [LLMConversationController::class, 'destroy'])->name('conversations.destroy');
        Route::get('conversations/{sessionId}/export', [LLMConversationController::class, 'export'])->name('conversations.export');
        
        // Knowledge Base
        Route::resource('knowledge-base', LLMKnowledgeBaseController::class)->parameters([
            'knowledge-base' => 'document'
        ]);
        Route::post('knowledge-base/{document}/index', [LLMKnowledgeBaseController::class, 'indexDocument'])->name('knowledge-base.index-doc');
        
        // Tool Definitions
        Route::resource('tools', LLMToolDefinitionController::class);
    });
