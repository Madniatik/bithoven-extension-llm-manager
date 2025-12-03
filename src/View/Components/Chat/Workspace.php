<?php

namespace Bithoven\LLMManager\View\Components\Chat;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConfiguration;

/**
 * LLM Chat Workspace Component
 * 
 * Master component for LLM chat interfaces with integrated monitor,
 * settings, and multiple layout modes.
 * 
 * @package Bithoven\LLMManager\View\Components\Chat
 */
class Workspace extends Component
{
    /**
     * The conversation session
     *
     * @var LLMConversationSession|null
     */
    public ?LLMConversationSession $session;

    /**
     * Available LLM configurations
     *
     * @var Collection
     */
    public Collection $configurations;

    /**
     * Layout mode: 'bubble', 'drawer', 'compact'
     *
     * @var string
     */
    public string $layout;

    /**
     * Show monitor panel
     *
     * @var bool
     */
    public bool $showMonitor;

    /**
     * Monitor layout: 'drawer', 'tabs', 'split-horizontal', 'split-vertical'
     *
     * @var string
     */
    public string $monitorLayout;

    /**
     * Component mode: 'full', 'demo', 'canvas-only'
     *
     * @var string
     */
    public string $mode;

    /**
     * Persist messages to database
     *
     * @var bool
     */
    public bool $persist;

    /**
     * Show toolbar with settings
     *
     * @var bool
     */
    public bool $showToolbar;

    /**
     * Create a new component instance.
     *
     * @param LLMConversationSession|null $session
     * @param Collection|null $configurations
     * @param string $layout
     * @param bool $showMonitor
     * @param string $monitorLayout
     * @param string $mode
     * @param bool $persist
     * @param bool $showToolbar
     */
    public function __construct(
        ?LLMConversationSession $session = null,
        ?Collection $configurations = null,
        string $layout = 'bubble',
        bool $showMonitor = false,
        string $monitorLayout = 'drawer',
        string $mode = 'full',
        bool $persist = true,
        bool $showToolbar = true
    ) {
        $this->session = $session;
        $this->configurations = $configurations ?? LLMConfiguration::where('is_active', true)->get();
        $this->layout = $layout;
        $this->showMonitor = $showMonitor;
        $this->monitorLayout = $monitorLayout;
        $this->mode = $mode;
        $this->persist = $persist;
        $this->showToolbar = $showToolbar;
    }

    /**
     * Get messages for the session
     *
     * @return Collection
     */
    public function getMessages(): Collection
    {
        if (!$this->session) {
            return collect();
        }

        return $this->session->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get monitor state from localStorage key
     *
     * @return string
     */
    public function getMonitorStorageKey(): string
    {
        return 'llm_chat_monitor_' . ($this->session?->id ?? 'demo');
    }

    /**
     * Check if component is in demo mode
     *
     * @return bool
     */
    public function isDemoMode(): bool
    {
        return $this->mode === 'demo';
    }

    /**
     * Check if component should show full workspace
     *
     * @return bool
     */
    public function isFullMode(): bool
    {
        return $this->mode === 'full';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('llm-manager::components.chat.workspace', [
            'messages' => $this->getMessages(),
        ]);
    }
}
