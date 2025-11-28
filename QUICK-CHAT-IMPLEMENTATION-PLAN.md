# Quick Chat - Implementation Plan (Design-First Approach)

**Fecha de Inicio:** 28 de noviembre de 2025  
**Estrategia:** Design-First (HTML/CSS → Mock Data → Logic → Components)  
**Objetivo:** Chat rápido sin persistencia de conversación

---

## 📐 FILOSOFÍA DEL PROYECTO

### Design-First Benefits
1. ✅ **Validación Visual Temprana** - Ver el resultado antes de escribir lógica
2. ✅ **Iteración Rápida** - Cambios de diseño sin romper código
3. ✅ **Componentes Bien Definidos** - Extraer solo lo validado
4. ✅ **Menos Refactoring** - La lógica se adapta al diseño aprobado

### Fases del Proyecto
```
FASE 1: Estructura & Routing (15 min)
    ↓
FASE 2: HTML/CSS Completo (2-3 horas)
    ↓
FASE 3: Mock Data & Estados (30 min)
    ↓
FASE 4: Validación & Iteración (1 hora)
    ↓
FASE 5: Documentación Diseño (15 min)
    ↓
FASE 6: Conectar Lógica (1-2 horas)
    ↓
FASE 7: Componentización (2-3 horas)
```

---

## 🎯 OBJETIVO: "Quick Chat" Feature

### Descripción
- **Ruta:** `/admin/llm/quick-chat`
- **Propósito:** Chat rápido sin guardar conversación en DB
- **Uso:** Testing, consultas rápidas, brainstorming
- **Persistencia:** Solo localStorage (opcional)

### Características Principales
- ✅ Selección de modelo LLM
- ✅ Ajustes de temperatura y tokens
- ✅ Streaming en tiempo real
- ✅ Renderizado Markdown
- ✅ Syntax highlighting
- ✅ Copiar mensajes
- ✅ Limpiar conversación
- ❌ NO guarda en DB (diferencia clave con `/conversations`)

---

## 📋 FASE 1: Estructura & Routing (15 min)

### ✅ Tareas

#### 1.1 Crear Controller
**Archivo:** `src/Http/Controllers/Admin/LLMQuickChatController.php`

```php
<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Bithoven\LLMManager\Models\LLMConfiguration;

class LLMQuickChatController extends Controller
{
    public function index()
    {
        $configurations = LLMConfiguration::active()->get();
        
        return view('llm-manager::admin.quick-chat.index', compact('configurations'));
    }
}
```

#### 1.2 Registrar Ruta
**Archivo:** `routes/web.php`

```php
// Quick Chat (no persistente)
Route::get('quick-chat', [LLMQuickChatController::class, 'index'])
    ->name('quick-chat');
```

**Posición:** Después de las rutas de `conversations`

#### 1.3 Crear Breadcrumb
**Archivo:** `/Users/madniatik/CODE/LARAVEL/BITHOVEN/CPANEL/routes/breadcrumbs.php`

```php
// Home > Dashboard > LLM Manager > Quick Chat
Breadcrumbs::for('admin.llm.quick-chat', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.llm.dashboard');
    $trail->push('Quick Chat', route('admin.llm.quick-chat'));
});
```

#### 1.4 Añadir al Menú Lateral
**Archivo:** Probablemente `config/menu.php` o similar del sistema principal

```php
[
    'title' => 'Quick Chat',
    'route' => 'admin.llm.quick-chat',
    'icon' => 'ki-duotone ki-messages',
    'bullet' => 'dot',
]
```

**Nota:** Verificar estructura real del menú en CPANEL

#### 1.5 Crear Vista Vacía
**Archivo:** `resources/views/admin/quick-chat/index.blade.php`

```blade
<x-default-layout>
    @section('title', 'Quick Chat')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.quick-chat') }}
    @endsection

    <div class="card">
        <div class="card-body">
            <h1>Quick Chat - Coming Soon</h1>
            <p>Configurations loaded: {{ $configurations->count() }}</p>
        </div>
    </div>
</x-default-layout>
```

### ✅ Validación Fase 1
- [ ] Ruta accesible: `http://localhost:8000/admin/llm/quick-chat`
- [ ] Breadcrumbs visibles
- [ ] Menú lateral con link "Quick Chat"
- [ ] Configuraciones cargadas correctamente
- [ ] No errores 404/500

---

## 🎨 FASE 2: HTML/CSS Completo (2-3 horas)

### Layout Principal

```
┌─────────────────────────────────────────────────────────┐
│ Breadcrumbs: Home > Dashboard > LLM > Quick Chat       │
├──────────────┬──────────────────────────────────────────┤
│              │  Messages Container (h-600px scroll)     │
│  Settings    │  ┌────────────────────────────────────┐  │
│  Sidebar     │  │ [User Message]                     │  │
│              │  │ [AI Response with Markdown]        │  │
│  - Model     │  │ [User Message]                     │  │
│  - Temp      │  │ [AI Thinking...]                   │  │
│  - Tokens    │  └────────────────────────────────────┘  │
│  - Context   │                                          │
│              │  ┌────────────────────────────────────┐  │
│  [Clear]     │  │ Textarea (auto-resize)             │  │
│              │  │ [Send] [Stop]                      │  │
│              │  └────────────────────────────────────┘  │
└──────────────┴──────────────────────────────────────────┘
```

### 2.1 Componentes a Diseñar

#### A. Settings Sidebar (col-xl-3)
**Componentes:**
- Model selector (dropdown con preview)
- Temperature slider (0-2, visual indicator)
- Max tokens input (100-4000)
- Context limit selector (5/10/20/50/all)
- System prompt textarea (opcional, colapsable)
- Clear conversation button (danger)

**Mejoras Visuales:**
- Card sticky (top: 20px)
- Model preview card con badges
- Range slider con labels "Focused" / "Creative"
- Tooltips con info adicional

#### B. Messages Container (col-xl-9)
**Tipos de Mensajes:**

##### User Message
```blade
<div class="message-group user-message mb-8 animate-fadeInUp">
    <div class="d-flex justify-content-end align-items-start gap-3">
        <div class="message-content">
            <!-- Header -->
            <div class="message-header d-flex align-items-center gap-2 mb-2 justify-content-end">
                <button class="btn btn-sm btn-icon btn-light-primary copy-btn opacity-0-hover">
                    <i class="ki-duotone ki-copy fs-5"></i>
                </button>
                <span class="text-muted fs-8">14:32:15</span>
                <span class="text-muted fs-7 fw-semibold">John Doe</span>
            </div>
            
            <!-- Bubble -->
            <div class="message-bubble bg-primary text-white p-4 rounded-3 shadow-sm">
                Explain quantum computing in simple terms
            </div>
        </div>
        
        <!-- Avatar -->
        <div class="symbol symbol-45px">
            <img src="{{ asset('storage/avatars/user-1.jpg') }}" alt="User" />
        </div>
    </div>
</div>
```

##### Assistant Message
```blade
<div class="message-group assistant-message mb-8 animate-fadeInUp">
    <div class="d-flex align-items-start gap-3">
        <!-- AI Icon -->
        <div class="symbol symbol-45px">
            <div class="symbol-label bg-light-primary">
                <i class="ki-duotone ki-robot text-primary fs-2x"></i>
            </div>
        </div>
        
        <div class="message-content flex-grow-1" style="max-width: 75%">
            <!-- Header -->
            <div class="message-header d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="text-muted fs-7 fw-semibold">AI Assistant</span>
                <span class="badge badge-light-primary badge-sm">ollama</span>
                <span class="badge badge-light-info badge-sm">qwen3:4b</span>
                <span class="text-muted fs-8">14:32:28</span>
                <span class="text-muted fs-8">• 245 tokens</span>
                <button class="btn btn-sm btn-icon btn-light copy-btn ms-auto opacity-0-hover">
                    <i class="ki-duotone ki-copy fs-5"></i>
                </button>
            </div>
            
            <!-- Bubble with Markdown -->
            <div class="message-bubble bg-light-secondary p-4 rounded-3 shadow-sm">
                <div class="message-content-markdown">
                    <!-- Markdown rendered content -->
                    <h4>Quantum Computing Simplified</h4>
                    <p>Imagine a regular computer bit as a coin that must be...</p>
                    <ul>
                        <li><strong>Classical bit:</strong> Either 0 or 1</li>
                        <li><strong>Quantum bit (qubit):</strong> Both 0 AND 1 simultaneously</li>
                    </ul>
                    <pre><code class="language-python">
# Example
quantum_bit = superposition(0, 1)
                    </code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
```

##### Thinking Indicator
```blade
<div class="message-group assistant-message thinking mb-8" id="thinking-indicator">
    <div class="d-flex align-items-start gap-3">
        <div class="symbol symbol-45px">
            <div class="symbol-label bg-light-primary">
                <i class="ki-duotone ki-robot text-primary fs-2x"></i>
            </div>
        </div>
        
        <div class="message-content">
            <div class="message-header d-flex align-items-center gap-2 mb-2">
                <span class="text-muted fs-7">AI is thinking</span>
                <span class="badge badge-light-primary badge-sm">ollama</span>
                <span class="badge badge-light-info badge-sm">qwen3:4b</span>
            </div>
            
            <div class="message-bubble bg-light p-4 rounded-3">
                <div class="typing-indicator d-flex gap-1">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>
        </div>
    </div>
</div>
```

##### Streaming Progress Bar
```blade
<div class="streaming-progress mb-4 animate-fadeInDown" id="streaming-progress" style="display: none;">
    <div class="card bg-light-primary border-primary">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-primary fw-bold fs-7">
                    <i class="ki-duotone ki-loading fs-5 rotate-animation me-2"></i>
                    Generating Response
                </span>
                <span class="text-primary fs-8">
                    <span id="current-tokens" class="fw-bold">0</span> / 
                    <span id="max-tokens">2000</span> tokens
                </span>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress h-10px mb-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                     role="progressbar" style="width: 0%" id="progress-bar"></div>
            </div>
            
            <!-- Stats -->
            <div class="d-flex justify-content-between">
                <span class="text-muted fs-9">
                    Speed: <span id="tokens-per-sec" class="fw-bold">0</span> tokens/sec
                </span>
                <span class="text-muted fs-9">
                    ETA: <span id="eta" class="fw-bold">--</span>s
                </span>
            </div>
        </div>
    </div>
</div>
```

#### C. Input Area (Fixed Bottom)
```blade
<div class="card mt-5">
    <div class="card-body p-4">
        <form id="quick-chat-form">
            <!-- Character Counter -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted fs-8">
                    <i class="ki-duotone ki-text fs-6"></i>
                    <span id="char-count">0</span> characters
                </span>
                <span class="text-muted fs-8">
                    Press <kbd>Ctrl</kbd> + <kbd>Enter</kbd> to send
                </span>
            </div>
            
            <!-- Textarea -->
            <textarea 
                class="form-control form-control-lg mb-3" 
                id="message-input" 
                rows="3"
                placeholder="Type your message here..."
                style="resize: none; overflow: hidden;"
            ></textarea>
            
            <!-- Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" id="clear-input-btn">
                    <i class="ki-duotone ki-cross fs-2"></i>
                    Clear
                </button>
                <button type="submit" class="btn btn-primary" id="send-btn">
                    <i class="ki-duotone ki-send fs-2"></i>
                    Send Message
                </button>
                <button type="button" class="btn btn-danger" id="stop-btn" style="display: none;">
                    <i class="ki-duotone ki-stop fs-2"></i>
                    Stop Generating
                </button>
            </div>
        </form>
    </div>
</div>
```

### 2.2 CSS Animations & Styles

**Archivo:** Inline en `@push('styles')` o crear `quick-chat.css`

```css
/* ============================================
   ANIMATIONS
   ============================================ */

/* Fade In Up (messages) */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.4s ease-out;
}

/* Fade In Down (progress bar) */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInDown {
    animation: fadeInDown 0.3s ease-out;
}

/* Typing Indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
}

.typing-indicator .dot {
    width: 8px;
    height: 8px;
    background-color: #7239EA;
    border-radius: 50%;
    animation: typingDot 1.4s infinite;
}

.typing-indicator .dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator .dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typingDot {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.7;
    }
    30% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

/* Rotate Animation (loading icon) */
@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.rotate-animation {
    animation: rotate 2s linear infinite;
}

/* ============================================
   MESSAGE BUBBLES
   ============================================ */

.message-bubble {
    position: relative;
    transition: all 0.2s ease;
}

.message-bubble:hover {
    transform: translateX(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* User message (right side) */
.user-message .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px !important;
}

/* Assistant message (left side) */
.assistant-message .message-bubble {
    background-color: #f5f8fa;
    border-bottom-left-radius: 4px !important;
}

/* ============================================
   HOVER EFFECTS
   ============================================ */

.opacity-0-hover {
    opacity: 0;
    transition: opacity 0.2s;
}

.message-group:hover .opacity-0-hover {
    opacity: 1;
}

.copy-btn {
    transition: all 0.2s;
}

.copy-btn:hover {
    transform: scale(1.1);
}

/* ============================================
   MARKDOWN CONTENT
   ============================================ */

.message-content-markdown h1,
.message-content-markdown h2,
.message-content-markdown h3,
.message-content-markdown h4 {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.message-content-markdown p {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.message-content-markdown ul,
.message-content-markdown ol {
    margin-bottom: 0.75rem;
    padding-left: 1.5rem;
}

.message-content-markdown li {
    margin-bottom: 0.25rem;
}

.message-content-markdown code {
    background-color: rgba(0, 0, 0, 0.05);
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.message-content-markdown pre {
    background-color: #2d2d2d;
    padding: 1rem;
    border-radius: 6px;
    overflow-x: auto;
    margin-bottom: 1rem;
    position: relative;
}

.message-content-markdown pre code {
    background-color: transparent;
    padding: 0;
    color: #f8f8f2;
}

/* Copy button for code blocks */
.message-content-markdown pre:hover::after {
    content: 'Copy';
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.message-content-markdown blockquote {
    border-left: 4px solid #ddd;
    padding-left: 1rem;
    margin-left: 0;
    color: #666;
    font-style: italic;
}

.message-content-markdown hr {
    margin: 1.5rem 0;
    border: none;
    border-top: 1px solid #e1e8ed;
}

.message-content-markdown a {
    color: #007bff;
    text-decoration: underline;
}

/* ============================================
   SCROLLBAR
   ============================================ */

.messages-container::-webkit-scrollbar {
    width: 8px;
}

.messages-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.messages-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* ============================================
   SMOOTH SCROLL
   ============================================ */

.messages-container {
    scroll-behavior: smooth;
}

/* ============================================
   INPUT AREA
   ============================================ */

#message-input:focus {
    border-color: #7239EA;
    box-shadow: 0 0 0 0.25rem rgba(114, 57, 234, 0.25);
}

kbd {
    background-color: #f7f7f7;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 2px 5px;
    font-size: 11px;
    font-family: monospace;
}
```

### 2.3 Estados del Sistema

**Estados a diseñar visualmente:**

1. **Idle (Esperando input)**
   - Send button activo
   - Stop button hidden
   - Progress bar hidden
   - Thinking indicator hidden

2. **Thinking (Iniciando stream)**
   - Send button disabled
   - Stop button visible
   - Thinking indicator visible
   - Progress bar hidden

3. **Streaming (Recibiendo chunks)**
   - Send button disabled
   - Stop button visible
   - Thinking indicator hidden
   - Progress bar visible con stats

4. **Complete (Stream terminado)**
   - Send button activo
   - Stop button hidden
   - Progress bar hidden
   - Checkmark animation (opcional)

5. **Error (Falló el stream)**
   - Send button activo
   - Stop button hidden
   - Error message visible (toast o inline)

### ✅ Validación Fase 2
- [ ] Layout responsive en mobile/tablet/desktop
- [ ] Colores consistentes con Metronic theme
- [ ] Iconos KI-Duotone renderizados
- [ ] Animaciones suaves (fade-in, typing dots)
- [ ] Copy buttons aparecen en hover
- [ ] Code blocks con syntax highlighting (Prism.js)
- [ ] Textarea auto-resize funciona
- [ ] Estados visuales claros
- [ ] Markdown renderizado correctamente

---

## 🎭 FASE 3: Mock Data & Estados (30 min)

### 3.1 Crear Archivo JavaScript Mock

**Archivo:** `resources/js/quick-chat-mock.js`

```javascript
/**
 * Mock Data para Quick Chat
 * Solo para validación de diseño - NO CONECTAR A BACKEND AÚN
 */

const QuickChatMock = {
    // Mock messages
    messages: [
        {
            id: 1,
            role: 'user',
            content: 'Explain quantum computing in simple terms',
            timestamp: '2025-11-28 14:32:15',
            user: {
                name: 'John Doe',
                avatar: 'storage/avatars/user-1.jpg'
            }
        },
        {
            id: 2,
            role: 'assistant',
            content: `# Quantum Computing Simplified

Imagine a regular computer bit as a coin that must be either heads (1) or tails (0).

**Classical bit:** Either 0 or 1  
**Quantum bit (qubit):** Both 0 AND 1 simultaneously (superposition)

\`\`\`python
# Example
quantum_bit = superposition(0, 1)
result = quantum_bit.measure()  # Collapses to 0 or 1
\`\`\`

This allows quantum computers to process exponentially more possibilities at once! 🚀`,
            timestamp: '2025-11-28 14:32:28',
            tokens: 245,
            provider: 'ollama',
            model: 'qwen3:4b'
        },
        {
            id: 3,
            role: 'user',
            content: 'Can you give me a practical example?',
            timestamp: '2025-11-28 14:33:10',
            user: {
                name: 'John Doe',
                avatar: 'storage/avatars/user-1.jpg'
            }
        }
    ],

    // Mock configurations
    configurations: [
        {
            id: 1,
            name: 'Ollama Qwen 3',
            provider: 'ollama',
            model: 'qwen3:4b',
            description: 'Fast responses, good for general questions'
        },
        {
            id: 2,
            name: 'OpenAI GPT-4o',
            provider: 'openai',
            model: 'gpt-4o',
            description: 'Premium model with advanced reasoning'
        },
        {
            id: 3,
            name: 'Ollama DeepSeek Coder',
            provider: 'ollama',
            model: 'deepseek-coder:6.7b',
            description: 'Specialized in coding tasks'
        }
    ],

    // Render mock messages
    renderMockMessages() {
        const container = document.getElementById('messages-container');
        if (!container) return;

        container.innerHTML = '';
        
        this.messages.forEach(msg => {
            const messageEl = this.createMessageElement(msg);
            container.appendChild(messageEl);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    },

    // Create message element
    createMessageElement(msg) {
        const div = document.createElement('div');
        div.className = `message-group ${msg.role}-message mb-8 animate-fadeInUp`;
        
        if (msg.role === 'user') {
            div.innerHTML = this.getUserMessageHTML(msg);
        } else {
            div.innerHTML = this.getAssistantMessageHTML(msg);
        }
        
        return div;
    },

    // User message HTML
    getUserMessageHTML(msg) {
        return `
            <div class="d-flex justify-content-end align-items-start gap-3">
                <div class="message-content">
                    <div class="message-header d-flex align-items-center gap-2 mb-2 justify-content-end">
                        <button class="btn btn-sm btn-icon btn-light-primary copy-btn opacity-0-hover">
                            <i class="ki-duotone ki-copy fs-5"></i>
                        </button>
                        <span class="text-muted fs-8">${msg.timestamp.split(' ')[1]}</span>
                        <span class="text-muted fs-7 fw-semibold">${msg.user.name}</span>
                    </div>
                    <div class="message-bubble bg-primary text-white p-4 rounded-3 shadow-sm">
                        ${msg.content}
                    </div>
                </div>
                <div class="symbol symbol-45px">
                    <img src="${msg.user.avatar}" alt="User" />
                </div>
            </div>
        `;
    },

    // Assistant message HTML
    getAssistantMessageHTML(msg) {
        return `
            <div class="d-flex align-items-start gap-3">
                <div class="symbol symbol-45px">
                    <div class="symbol-label bg-light-primary">
                        <i class="ki-duotone ki-robot text-primary fs-2x"></i>
                    </div>
                </div>
                <div class="message-content flex-grow-1" style="max-width: 75%">
                    <div class="message-header d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="text-muted fs-7 fw-semibold">AI Assistant</span>
                        <span class="badge badge-light-primary badge-sm">${msg.provider}</span>
                        <span class="badge badge-light-info badge-sm">${msg.model}</span>
                        <span class="text-muted fs-8">${msg.timestamp.split(' ')[1]}</span>
                        <span class="text-muted fs-8">• ${msg.tokens} tokens</span>
                        <button class="btn btn-sm btn-icon btn-light copy-btn ms-auto opacity-0-hover">
                            <i class="ki-duotone ki-copy fs-5"></i>
                        </button>
                    </div>
                    <div class="message-bubble bg-light-secondary p-4 rounded-3 shadow-sm">
                        <div class="message-content-markdown">
                            ${marked.parse(msg.content)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    // Simulate typing state
    showThinking() {
        const container = document.getElementById('messages-container');
        const thinkingHTML = `
            <div class="message-group assistant-message thinking mb-8" id="thinking-indicator">
                <div class="d-flex align-items-start gap-3">
                    <div class="symbol symbol-45px">
                        <div class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-robot text-primary fs-2x"></i>
                        </div>
                    </div>
                    <div class="message-content">
                        <div class="message-header d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted fs-7">AI is thinking</span>
                            <span class="badge badge-light-primary badge-sm">ollama</span>
                            <span class="badge badge-light-info badge-sm">qwen3:4b</span>
                        </div>
                        <div class="message-bubble bg-light p-4 rounded-3">
                            <div class="typing-indicator d-flex gap-1">
                                <span class="dot"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', thinkingHTML);
        container.scrollTop = container.scrollHeight;
    },

    // Remove thinking indicator
    hideThinking() {
        const thinking = document.getElementById('thinking-indicator');
        if (thinking) thinking.remove();
    },

    // Show progress bar
    showProgress() {
        const progress = document.getElementById('streaming-progress');
        if (progress) progress.style.display = 'block';
    },

    // Hide progress bar
    hideProgress() {
        const progress = document.getElementById('streaming-progress');
        if (progress) progress.style.display = 'none';
    },

    // Simulate streaming
    async simulateStreaming() {
        this.showThinking();
        await this.sleep(1000);
        
        this.hideThinking();
        this.showProgress();
        
        // Simulate progress
        for (let i = 0; i <= 100; i += 5) {
            document.getElementById('progress-bar').style.width = i + '%';
            document.getElementById('current-tokens').textContent = Math.floor((i / 100) * 245);
            document.getElementById('tokens-per-sec').textContent = (Math.random() * 50 + 20).toFixed(1);
            await this.sleep(100);
        }
        
        this.hideProgress();
        
        // Add mock response
        const mockResponse = {
            id: Date.now(),
            role: 'assistant',
            content: 'This is a mock response. Real streaming will replace this.',
            timestamp: new Date().toISOString().slice(0, 19).replace('T', ' '),
            tokens: 245,
            provider: 'ollama',
            model: 'qwen3:4b'
        };
        
        const messageEl = this.createMessageElement(mockResponse);
        document.getElementById('messages-container').appendChild(messageEl);
    },

    // Utility: sleep
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    // Initialize
    init() {
        console.log('QuickChatMock initialized');
        this.renderMockMessages();
        
        // Add event listeners
        document.getElementById('send-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.simulateStreaming();
        });
    }
};

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    QuickChatMock.init();
});
```

### 3.2 Incluir en Vista

**En `index.blade.php`:**

```blade
@push('scripts')
<!-- Marked.js for Markdown (ya incluido globalmente) -->
<script src="https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js"></script>

<!-- Mock data (SOLO PARA DISEÑO) -->
<script src="{{ asset('js/quick-chat-mock.js') }}"></script>
@endpush
```

### ✅ Validación Fase 3
- [ ] Mock messages renderizan correctamente
- [ ] Markdown se parsea con marked.js
- [ ] Thinking indicator funciona
- [ ] Progress bar anima correctamente
- [ ] Simulación de streaming completa
- [ ] Copy buttons funcionan
- [ ] Estados visuales correctos

---

## ✅ FASE 4: Validación & Iteración (1 hora)

### Checklist de Validación

#### Responsive Design
- [ ] Desktop (1920px+): Sidebar 3 cols, messages 9 cols
- [ ] Tablet (768-1024px): Sidebar colapsa, full width messages
- [ ] Mobile (<768px): Stack vertical, settings en modal

#### Visual Consistency
- [ ] Colores Metronic: Primary (#7239EA), Secondary, Success, Danger
- [ ] Iconos KI-Duotone cargados
- [ ] Tipografía consistente (Inter font)
- [ ] Spacing uniforme (mb-8, gap-3, p-4)

#### Animaciones
- [ ] Fade-in messages suaves (0.4s)
- [ ] Typing dots animados (1.4s loop)
- [ ] Progress bar striped animation
- [ ] Hover effects en mensajes
- [ ] Smooth scroll

#### Accesibilidad
- [ ] Contraste de texto adecuado (WCAG AA)
- [ ] Focus states visibles
- [ ] Keyboard navigation (Tab, Enter, Esc)
- [ ] Screen reader friendly (ARIA labels)

#### Estados Visuales
- [ ] Idle: Todo normal
- [ ] Thinking: Dots animados
- [ ] Streaming: Progress bar visible
- [ ] Complete: Mensaje renderizado
- [ ] Error: Toast de error visible

#### Funcionalidad Mock
- [ ] Send button dispara simulación
- [ ] Stop button visible durante stream
- [ ] Clear conversation limpia messages
- [ ] Copy buttons copian al clipboard
- [ ] Textarea auto-resize funciona

### Testing en Navegadores
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (si disponible)

### Ajustes Comunes
- Spacing entre mensajes
- Tamaño de avatares
- Ancho máximo de bubbles
- Altura del textarea
- Velocidad de animaciones

---

## 📚 FASE 5: Documentación Diseño (15 min)

### Crear Archivo de Especificaciones

**Archivo:** `resources/views/admin/quick-chat/DESIGN-SPECS.md`

```markdown
# Quick Chat - Design Specifications

**Created:** 28/11/2025  
**Status:** Design Complete, Logic Pending

---

## Layout Structure

### Grid System
- Container: `row g-5`
- Sidebar: `col-xl-3` (sticky-top)
- Messages: `col-xl-9`

### Breakpoints
- Desktop: ≥1200px (sidebar visible)
- Tablet: 768-1199px (sidebar collapses to top)
- Mobile: <768px (stack vertical)

---

## Components

### 1. Message Bubble (User)
**Classes:** `.message-group .user-message`
- Background: Linear gradient (#667eea → #764ba2)
- Text: White
- Alignment: Right
- Max width: None (content-based)
- Border radius: 12px (except bottom-right: 4px)

### 2. Message Bubble (Assistant)
**Classes:** `.message-group .assistant-message`
- Background: #f5f8fa
- Text: Dark gray
- Alignment: Left
- Max width: 75%
- Border radius: 12px (except bottom-left: 4px)

### 3. Thinking Indicator
**Element:** `#thinking-indicator`
- Typing dots: 3 dots, 8px each
- Animation: 1.4s infinite, staggered
- Color: Primary (#7239EA)

### 4. Progress Bar
**Element:** `#streaming-progress`
- Height: 10px
- Striped: Yes
- Animated: Yes
- Shows: Current tokens, speed, ETA

### 5. Settings Sidebar
**Sticky positioning:** `top: 20px`
- Model selector: Dropdown with preview card
- Temperature: Range slider (0-2, step 0.1)
- Max tokens: Number input (100-4000, step 100)
- Clear button: Danger style

---

## Animations

### fadeInUp
- Duration: 0.4s
- Easing: ease-out
- Transform: translateY(20px) → 0

### typingDot
- Duration: 1.4s
- Infinite: Yes
- Stagger: 0.2s per dot

### rotate
- Duration: 2s
- Linear: Yes
- Infinite: Yes

---

## CSS Classes Reference

| Class | Purpose |
|-------|---------|
| `.message-group` | Message container |
| `.user-message` | User message styling |
| `.assistant-message` | AI message styling |
| `.thinking` | Thinking state |
| `.animate-fadeInUp` | Fade-in animation |
| `.opacity-0-hover` | Hidden until hover |
| `.copy-btn` | Copy to clipboard button |
| `.message-content-markdown` | Markdown rendered content |

---

## Icons Used

- `ki-duotone ki-robot` - AI avatar
- `ki-duotone ki-send` - Send button
- `ki-duotone ki-stop` - Stop button
- `ki-duotone ki-copy` - Copy button
- `ki-duotone ki-trash` - Clear button
- `ki-duotone ki-loading` - Loading spinner

---

## Color Palette

| Color | Hex | Usage |
|-------|-----|-------|
| Primary | #7239EA | Buttons, badges, accents |
| Secondary | #f5f8fa | AI message background |
| Success | #50CD89 | Success states |
| Danger | #F1416C | Error states, delete |
| Warning | #FFC700 | Warning states |
| Dark | #181C32 | Text primary |
| Muted | #A1A5B7 | Text secondary |

---

## Next Steps (After Design Approval)

1. ✅ Replace mock data with real EventSource
2. ✅ Implement localStorage persistence
3. ✅ Add keyboard shortcuts (Ctrl+Enter)
4. ✅ Extract to reusable Blade component
5. ✅ Create JavaScript class `LLMChatStreaming`
6. ✅ Migrate other views to use new design
```

### ✅ Validación Fase 5
- [ ] Documentación completa y clara
- [ ] Screenshots de cada estado incluidos (opcional)
- [ ] CSS classes documentadas
- [ ] Próximos pasos definidos

---

## 🚀 FASE 6: Conectar Lógica (1-2 horas)

**⚠️ ESTA FASE NO SE EJECUTA HASTA APROBAR EL DISEÑO**

### 6.1 Crear Endpoint de Streaming

**Controller method:**
```php
public function stream(Request $request)
{
    // Similar a LLMConversationController::streamReply
    // pero SIN guardar en DB
}
```

### 6.2 Reemplazar Mock con EventSource Real

**JavaScript:**
```javascript
class QuickChatStreaming {
    constructor() {
        this.eventSource = null;
        // ... similar a ConversationStreaming
    }
    
    startStreaming() {
        const url = '/admin/llm/quick-chat/stream';
        this.eventSource = new EventSource(url + '?' + params);
        // ... handle events
    }
}
```

### 6.3 localStorage Persistence (Opcional)

```javascript
saveToLocalStorage() {
    const messages = this.getAllMessages();
    localStorage.setItem('quick-chat-history', JSON.stringify(messages));
}

loadFromLocalStorage() {
    const saved = localStorage.getItem('quick-chat-history');
    return saved ? JSON.parse(saved) : [];
}
```

---

## 🧩 FASE 7: Componentización (2-3 horas)

**⚠️ ESTA FASE ES PARA DESPUÉS DE VALIDAR QUICK CHAT FUNCIONANDO**

### 7.1 Extraer Componente Blade

**Archivo:** `resources/views/components/llm-chat-window.blade.php`

```blade
@props([
    'messages' => [],
    'configurations' => [],
    'endpoint' => '',
    'showSettings' => true,
    'persistent' => false,
])

<!-- Reusable chat window component -->
```

### 7.2 Crear Clase JavaScript Reutilizable

**Archivo:** `public/js/llm-chat-streaming.js`

```javascript
class LLMChatStreaming {
    constructor(config) {
        // Reusable streaming class
    }
}
```

### 7.3 Migrar Vistas Existentes

1. `conversations/show.blade.php` → usar `<x-llm-chat-window>`
2. `stream/test.blade.php` → usar `<x-llm-chat-window>`

---

## 📊 RESUMEN DE TIEMPOS

| Fase | Descripción | Tiempo Estimado | Status |
|------|-------------|-----------------|--------|
| 1 | Estructura & Routing | 15 min | ⏳ Pending |
| 2 | HTML/CSS Completo | 2-3 horas | ⏳ Pending |
| 3 | Mock Data & Estados | 30 min | ⏳ Pending |
| 4 | Validación & Iteración | 1 hora | ⏳ Pending |
| 5 | Documentación Diseño | 15 min | ⏳ Pending |
| **SUBTOTAL DISEÑO** | **4-5 horas** | | |
| 6 | Conectar Lógica | 1-2 horas | 🔒 Bloqueado |
| 7 | Componentización | 2-3 horas | 🔒 Bloqueado |
| **TOTAL PROYECTO** | **7-10 horas** | | |

---

## ✅ CHECKLIST GENERAL

### Fase 1: Estructura ✅
- [ ] Controller creado
- [ ] Ruta registrada
- [ ] Breadcrumb añadido
- [ ] Menú lateral actualizado
- [ ] Vista vacía funcional

### Fase 2: Diseño HTML/CSS ✅
- [ ] Settings sidebar diseñado
- [ ] User messages diseñados
- [ ] Assistant messages diseñados
- [ ] Thinking indicator diseñado
- [ ] Progress bar diseñado
- [ ] Input area diseñada
- [ ] Animaciones CSS implementadas
- [ ] Responsive design validado

### Fase 3: Mock Data ✅
- [ ] Mock messages creados
- [ ] Mock configurations creados
- [ ] Render function implementada
- [ ] Simulación de streaming funcional

### Fase 4: Validación ✅
- [ ] Testing en 3+ browsers
- [ ] Testing responsive
- [ ] Testing accesibilidad
- [ ] Ajustes visuales completados

### Fase 5: Documentación ✅
- [ ] DESIGN-SPECS.md creado
- [ ] CSS classes documentadas
- [ ] Componentes listados
- [ ] Próximos pasos definidos

### Fase 6: Lógica (Bloqueado) 🔒
- [ ] Endpoint creado
- [ ] EventSource implementado
- [ ] localStorage opcional
- [ ] Testing funcional

### Fase 7: Componentes (Bloqueado) 🔒
- [ ] Blade component extraído
- [ ] JS class creada
- [ ] Vistas migradas
- [ ] Testing completo

---

## 🎯 ENTREGABLES

### Al finalizar Fase 5 (Diseño):
1. ✅ Vista funcional en `/admin/llm/quick-chat`
2. ✅ HTML/CSS completo con mock data
3. ✅ Todos los estados visuales diseñados
4. ✅ Animaciones y microinteracciones
5. ✅ Responsive design validado
6. ✅ Documentación completa
7. ❌ NO tiene lógica real (solo mock)

### Al finalizar Fase 7 (Completo):
1. ✅ Quick Chat 100% funcional
2. ✅ Componentes reutilizables creados
3. ✅ Vistas existentes migradas
4. ✅ Sistema unificado y mantenible

---

## 📝 NOTAS IMPORTANTES

### Design-First Advantages
- ✅ Validación visual antes de programar
- ✅ Cambios rápidos sin romper código
- ✅ Componentes bien definidos
- ✅ Menos refactoring posterior

### Workflow
1. **Diseñar** → Aprobar → **Programar**
2. NO mezclar diseño con lógica
3. Mock data hasta aprobar diseño
4. Documentar cada decisión

### Git Commits Sugeridos
- `feat(llm): add quick-chat routing and structure`
- `feat(llm): implement quick-chat HTML/CSS design`
- `feat(llm): add mock data for quick-chat validation`
- `docs(llm): document quick-chat design specs`
- `feat(llm): connect quick-chat to streaming logic`
- `refactor(llm): extract reusable chat components`

---

**Estado Actual:** Plan creado, esperando inicio de Fase 1  
**Próximo Paso:** Crear Controller + Ruta + Vista vacía  
**Bloqueadores:** Ninguno

---

_Este documento se actualizará conforme avance el desarrollo._
