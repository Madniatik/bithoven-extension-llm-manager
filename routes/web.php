<?php

use Illuminate\Support\Facades\Route;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMConfigurationController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMUsageStatsController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMPromptTemplateController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMConversationController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMKnowledgeBaseController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMToolDefinitionController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMStreamController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMActivityController;
use Bithoven\LLMManager\Http\Controllers\Admin\LLMQuickChatController;

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
        
        // Configurations (Legacy routes - only keeping necessary ones)
        Route::get('configurations', [LLMConfigurationController::class, 'index'])->name('configurations.index');
        Route::post('configurations/test', [LLMConfigurationController::class, 'testConnection'])->name('configurations.test');
        Route::post('configurations/{configuration}/toggle', [LLMConfigurationController::class, 'toggleActive'])->name('configurations.toggle');
        Route::delete('configurations/{configuration}', [LLMConfigurationController::class, 'destroy'])->name('configurations.destroy');
        
        // Usage Statistics
        Route::get('statistics', [LLMUsageStatsController::class, 'index'])->name('statistics.index');
        Route::get('statistics/export', [LLMUsageStatsController::class, 'export'])->name('statistics.export');
        
        // Prompt Templates
        Route::resource('prompts', LLMPromptTemplateController::class)->parameters([
            'prompts' => 'template'
        ]);
        
        // Conversations
        Route::get('conversations', [LLMConversationController::class, 'index'])->name('conversations.index');
        Route::get('conversations/create', [LLMConversationController::class, 'create'])->name('conversations.create');
        Route::post('conversations', [LLMConversationController::class, 'store'])->name('conversations.store');
        Route::get('conversations/{id}', [LLMConversationController::class, 'show'])->name('conversations.show')->where('id', '[0-9]+');
        Route::delete('conversations/{id}', [LLMConversationController::class, 'destroy'])->name('conversations.destroy')->where('id', '[0-9]+');
        Route::get('conversations/{id}/export', [LLMConversationController::class, 'export'])->name('conversations.export')->where('id', '[0-9]+');
        Route::get('conversations/{id}/stream-reply', [LLMConversationController::class, 'streamReply'])->name('conversations.stream-reply')->where('id', '[0-9]+');
        
        // Quick Chat
        Route::get('quick-chat', [LLMQuickChatController::class, 'index'])->name('quick-chat');
        Route::post('quick-chat/stream', [LLMQuickChatController::class, 'stream'])->name('quick-chat.stream');
        Route::post('quick-chat/save', [LLMQuickChatController::class, 'save'])->name('quick-chat.save');
        Route::get('quick-chat/new', [LLMQuickChatController::class, 'newChat'])->name('quick-chat.new');
        Route::get('messages/{messageId}/raw', [LLMQuickChatController::class, 'getRawMessage'])->name('messages.raw')->where('messageId', '[0-9]+');
        
        // Knowledge Base
        Route::resource('knowledge-base', LLMKnowledgeBaseController::class)->parameters([
            'knowledge-base' => 'document'
        ]);
        Route::post('knowledge-base/{document}/index', [LLMKnowledgeBaseController::class, 'indexDocument'])->name('knowledge-base.index-doc');
        
        // Tool Definitions
        Route::resource('tools', LLMToolDefinitionController::class);
        
        // Streaming Test & Endpoints
        Route::get('stream/test', [LLMStreamController::class, 'test'])->name('stream.test');
        Route::get('stream/stream', [LLMStreamController::class, 'stream'])->name('stream.stream');
        Route::get('stream/conversation', [LLMStreamController::class, 'conversationStream'])->name('stream.conversation');
        
        // Activity Logs
        Route::get('activity', [LLMActivityController::class, 'index'])->name('activity.index');
        Route::get('activity/{id}', [LLMActivityController::class, 'show'])->name('activity.show');
        Route::get('activity-export/csv', [LLMActivityController::class, 'export'])->name('activity.export');
        Route::get('activity-export/json', [LLMActivityController::class, 'exportJson'])->name('activity.export-json');
        
        // New model-based routes (tab interface)
        Route::get('models/{model}', [\Bithoven\LLMManager\Http\Controllers\Admin\LLMModelController::class, 'show'])->name('models.show');
        Route::post('models', [\Bithoven\LLMManager\Http\Controllers\Admin\LLMModelController::class, 'store'])->name('models.store');
        Route::put('models/{model}', [\Bithoven\LLMManager\Http\Controllers\Admin\LLMModelController::class, 'update'])->name('models.update');
        Route::put('models/{model}/advanced', [\Bithoven\LLMManager\Http\Controllers\Admin\LLMModelController::class, 'updateAdvanced'])->name('models.update-advanced');
    });
