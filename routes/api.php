<?php

use Illuminate\Support\Facades\Route;
use Bithoven\LLMManager\Http\Controllers\API\LLMGenerateController;
use Bithoven\LLMManager\Http\Controllers\API\LLMChatController;
use Bithoven\LLMManager\Http\Controllers\API\LLMRAGController;
use Bithoven\LLMManager\Http\Controllers\API\LLMToolController;
use Bithoven\LLMManager\Http\Controllers\API\LLMWorkflowController;

/*
|--------------------------------------------------------------------------
| LLM Manager API Routes
|--------------------------------------------------------------------------
|
| Public API routes for extensions to interact with LLM Manager
|
*/

Route::prefix('api/llm')
    ->middleware(['api', 'llm.api'])
    ->name('api.llm.')
    ->group(function () {
        
        // Core LLM Operations
        Route::post('generate', LLMGenerateController::class)->name('generate');
        
        // Conversations
        Route::post('chat/start', [LLMChatController::class, 'start'])->name('chat.start');
        Route::post('chat/send', [LLMChatController::class, 'send'])->name('chat.send');
        Route::post('chat/end', [LLMChatController::class, 'end'])->name('chat.end');
        
        // RAG
        Route::post('rag/search', [LLMRAGController::class, 'search'])->name('rag.search');
        Route::post('rag/generate', [LLMRAGController::class, 'generate'])->name('rag.generate');
        
        // Tools
        Route::post('tools/execute', [LLMToolController::class, 'execute'])->name('tools.execute');
        
        // Workflows
        Route::post('workflows/execute', [LLMWorkflowController::class, 'execute'])->name('workflows.execute');
    });
