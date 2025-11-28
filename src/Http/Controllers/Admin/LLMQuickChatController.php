<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMQuickChatController extends Controller
{
    /**
     * Display the Quick Chat interface.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Load active LLM configurations
        $configurations = LLMConfiguration::active()->get();
        
        return view('llm-manager::admin.quick-chat.index', compact('configurations'));
    }
}
