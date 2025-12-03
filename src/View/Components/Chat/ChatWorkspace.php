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
     * Crear nueva instancia del componente
     *
     * @param LLMConversationSession|null $session
     * @param Collection $configurations
     * @param bool $showMonitor
     * @param bool $monitorOpen
     */
    public function __construct(
        $session = null,
        $configurations = null,
        bool $showMonitor = false,
        bool $monitorOpen = false
    ) {
        $this->session = $session;
        $this->configurations = $configurations ?? collect([]);
        $this->showMonitor = $showMonitor;
        $this->monitorOpen = $monitorOpen;
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

        return $this->session->messages()
            ->with('user')
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
     * Obtener ID único para el monitor
     *
     * @return string
     */
    public function getMonitorId(): string
    {
        return 'monitor-' . ($this->session?->id ?? uniqid());
    }

    /**
     * Renderizar componente
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('llm-manager::components.chat.ChatWorkspace', [
            'messages' => $this->getMessages(),
            'monitorId' => $this->getMonitorId(),
        ]);
    }
}
