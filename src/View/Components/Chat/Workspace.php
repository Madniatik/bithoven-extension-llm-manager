<?php

namespace Bithoven\LLMManager\View\Components\Chat;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Bithoven\LLMManager\Models\LLMConversationSession;
use Bithoven\LLMManager\Models\LLMConfiguration;
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

/**
 * LLM Chat Workspace Component
 * 
 * Master component for LLM chat interfaces with integrated monitor,
 * settings, and multiple layout modes.
 * 
 * Supports both legacy props (backward compatible) and new config array system.
 * 
 * @package Bithoven\LLMManager\View\Components\Chat
 * @version 1.0.7
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
     * Validated configuration array
     *
     * @var array
     */
    public array $config;

    /**
     * Legacy props (backward compatibility)
     * These are deprecated and will be removed in v2.0
     */
    
    /** @deprecated Use $config['ui']['layout']['chat'] */
    public string $layout;

    /** @deprecated Use $config['features']['monitor']['enabled'] */
    public bool $showMonitor;

    /** @deprecated Use $config['ui']['layout']['monitor'] */
    public string $monitorLayout;

    /** @deprecated Use $config['ui']['mode'] */
    public string $mode;

    /** @deprecated Use $config['features']['persistence'] */
    public bool $persist;

    /** @deprecated Use $config['features']['toolbar'] */
    public bool $showToolbar;

    /**
     * Create a new component instance.
     *
     * @param LLMConversationSession|null $session Conversation session
     * @param Collection|null $configurations Available LLM configurations
     * @param array|null $config New config array system (recommended)
     * @param string $layout LEGACY: Chat layout mode
     * @param bool $showMonitor LEGACY: Show monitor panel
     * @param string $monitorLayout LEGACY: Monitor layout
     * @param string $mode LEGACY: Component mode
     * @param bool $persist LEGACY: Persist messages
     * @param bool $showToolbar LEGACY: Show toolbar
     */
    public function __construct(
        ?LLMConversationSession $session = null,
        ?Collection $configurations = null,
        ?array $config = null,
        // Legacy props (backward compatibility)
        string $layout = 'bubble',
        bool $showMonitor = false,
        string $monitorLayout = 'drawer',
        string $mode = 'full',
        bool $persist = true,
        bool $showToolbar = true
    ) {
        $this->session = $session;
        $this->configurations = $configurations ?? LLMConfiguration::where('is_active', true)->get();

        // CONFIG SYSTEM: Decide entre config array (nuevo) o legacy props
        if ($config !== null) {
            // NEW SYSTEM: Validar y usar config array
            $this->config = ChatWorkspaceConfigValidator::validate($config);
            
            // Map config to legacy props (backward compatibility)
            $this->layout = $this->config['ui']['layout']['chat'];
            $this->showMonitor = $this->config['features']['monitor']['enabled'];
            $this->monitorLayout = $this->config['ui']['layout']['monitor'];
            $this->mode = $this->config['ui']['mode'];
            $this->persist = $this->config['features']['persistence'];
            $this->showToolbar = $this->config['features']['toolbar'];
        } else {
            // LEGACY SYSTEM: Construir config array desde legacy props
            $this->layout = $layout;
            $this->showMonitor = $showMonitor;
            $this->monitorLayout = $monitorLayout;
            $this->mode = $mode;
            $this->persist = $persist;
            $this->showToolbar = $showToolbar;
            
            // Build config array from legacy props
            $this->config = ChatWorkspaceConfigValidator::validate([
                'features' => [
                    'monitor' => [
                        'enabled' => $showMonitor,
                    ],
                    'persistence' => $persist,
                    'toolbar' => $showToolbar,
                ],
                'ui' => [
                    'layout' => [
                        'chat' => $layout,
                        'monitor' => $monitorLayout,
                    ],
                    'mode' => $mode,
                ],
            ]);
        }
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
     * Check if a specific monitor tab is enabled
     *
     * @param string $tab Tab name: 'console', 'request_inspector', 'activity_log'
     * @return bool
     */
    public function isMonitorTabEnabled(string $tab): bool
    {
        return $this->config['features']['monitor']['tabs'][$tab] ?? false;
    }

    /**
     * Check if a specific toolbar button is enabled
     *
     * @param string $button Button name: 'new_chat', 'clear', 'settings', 'download', 'monitor_toggle'
     * @return bool
     */
    public function isButtonEnabled(string $button): bool
    {
        return $this->config['ui']['buttons'][$button] ?? false;
    }

    /**
     * Check if settings panel is enabled
     *
     * @return bool
     */
    public function hasSettingsPanel(): bool
    {
        return $this->config['features']['settings_panel'] ?? true;
    }

    /**
     * Check if monitor should be open by default
     *
     * @return bool
     */
    public function isMonitorOpenByDefault(): bool
    {
        return $this->config['features']['monitor']['default_open'] ?? true;
    }

    /**
     * Get custom CSS class for the workspace
     *
     * @return string
     */
    public function getCustomCssClass(): string
    {
        return $this->config['advanced']['custom_css_class'] ?? '';
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->config['advanced']['debug_mode'] ?? false;
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
            'config' => $this->config, // Pass config to view
        ]);
    }
}
