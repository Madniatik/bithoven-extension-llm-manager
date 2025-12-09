<?php

namespace Bithoven\LLMManager\View\Components\Chat;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Bithoven\LLMManager\Models\LLMConversationSession;

class ChatWorkspace extends Component
{
    /**
     * Sesión de conversación
     *
     * @var LLMConversationSession|null
     */
    public $session;

    /**
     * Configuraciones LLM disponibles
     *
     * @var Collection
     */
    public $configurations;

    /**
     * Mostrar monitor de streaming
     *
     * @var bool
     */
    public $showMonitor;

    /**
     * Estado inicial del monitor (abierto/cerrado)
     *
     * @var bool
     */
    public $monitorOpen;

    /**
     * Layout del monitor
     *
     * @var string
     */
    public $monitorLayout;

    /**
     * Config array (validated configuration)
     *
     * @var array
     */
    public $config;

    /**
     * Crear nueva instancia del componente
     *
     * @param LLMConversationSession|null $session
     * @param Collection $configurations
     * @param array|null $config New config array system (recommended)
     * @param bool $showMonitor LEGACY: Show monitor panel
     * @param bool $monitorOpen LEGACY: Monitor initial state
     * @param string $monitorLayout LEGACY: Monitor layout 'sidebar' | 'split-horizontal'
     */
    public function __construct(
        $session = null,
        $configurations = null,
        ?array $config = null,
        // Legacy props (backward compatibility)
        bool $showMonitor = false,
        bool $monitorOpen = false,
        string $monitorLayout = 'sidebar'
    ) {
        $this->session = $session;
        $this->configurations = $configurations ?? collect([]);

        // CONFIG SYSTEM: Decide entre config array (nuevo) o legacy props
        if ($config !== null) {
            // NEW SYSTEM: Use config array directly
            $this->config = $config;
            
            // Map config to legacy props (backward compatibility)
            $this->showMonitor = $this->config['features']['monitor']['enabled'] ?? false;
            $this->monitorOpen = $this->config['features']['monitor']['default_open'] ?? false;
            $this->monitorLayout = $this->config['ui']['layout']['monitor'] ?? 'sidebar';
        } else {
            // LEGACY SYSTEM: Build config array from legacy props
            $this->showMonitor = $showMonitor;
            $this->monitorOpen = $monitorOpen;
            $this->monitorLayout = $monitorLayout;
            
            // Build minimal config array for views
            $this->config = [
                'features' => [
                    'monitor' => [
                        'enabled' => $showMonitor,
                        'default_open' => $monitorOpen,
                        'tabs' => [
                            'console' => true,
                            'activity_log' => true,
                            'request_inspector' => true,
                        ],
                    ],
                    'settings_panel' => true,
                ],
                'ui' => [
                    'layout' => [
                        'monitor' => $monitorLayout,
                    ],
                ],
                'performance' => [
                    'lazy_load_tabs' => true,
                    'cache_preferences' => true,
                ],
                'advanced' => [
                    'custom_css_class' => '',
                ],
            ];
        }
    }

    /**
     * Obtener mensajes de la sesión
     *
     * @return Collection
     */
    public function getMessages(): Collection
    {
        if (!$this->session) {
            return collect([]);
        }

        // Handle temporary session (stdClass from Quick Chat)
        if (is_object($this->session) && !($this->session instanceof LLMConversationSession)) {
            // Temporary session has messages as property (array/collection)
            return collect($this->session->messages ?? []);
        }

        // Handle Eloquent model (Conversation)
        return $this->session->messages()
            ->with(['user', 'llmConfiguration'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Verificar si hay sesión activa
     *
     * @return bool
     */
    public function hasSession(): bool
    {
        return $this->session !== null;
    }

    /**
     * Check if session is temporary (not persisted to DB)
     *
     * @return bool
     */
    public function isTemporarySession(): bool
    {
        if (!$this->session) {
            return false;
        }

        // Temporary sessions are stdClass objects or have null ID
        return !($this->session instanceof LLMConversationSession) || $this->session->id === null;
    }

    /**
     * Obtener ID único para el monitor
     *
     * @return string
     */
    public function getMonitorId(): string
    {
        return 'monitor-' . ($this->session?->id ?? uniqid());
    }

    /**
     * Check if specific monitor tab is enabled
     *
     * @param string $tab Tab name (console, activity_log, request_inspector)
     * @return bool
     */
    public function isMonitorTabEnabled(string $tab): bool
    {
        return $this->config['features']['monitor']['tabs'][$tab] ?? false;
    }

    /**
     * Renderizar componente
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('llm-manager::components.chat.chat-workspace', [
            'messages' => $this->getMessages(),
            'monitorId' => $this->getMonitorId(),
            'config' => $this->config,
        ]);
    }
}
