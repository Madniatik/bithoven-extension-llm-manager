# Chat Workspace Component - Configuration System Plan

**Parent Plan:** [PLAN-v1.0.7.md](./PLAN-v1.0.7.md)  
**Fecha de Creación:** 9 de diciembre de 2025, 09:00  
**Versión Objetivo:** v1.0.7 (feature adicional)  
**Estimación de Tiempo:** 12-15 horas  
**Prioridad:** MEDIA (extensibilidad futura)

---

## 📋 RESUMEN EJECUTIVO

Rediseñar el sistema de configuración del componente `Workspace.php` para soportar configuración granular mediante **array asociativo** en lugar de **props individuales**, permitiendo:

1. **Reutilización del componente** en diferentes contextos (Quick Chat, Conversations, otras extensiones)
2. **Configuración granular** de features (Monitor tabs, UI elements, buttons)
3. **Performance optimization** (carga condicional de JS/CSS por feature)
4. **Backward compatibility** (soporte para props legacy + config array)
5. **Extensibilidad** (agregar opciones sin breaking changes)
6. **Settings Panel UI** (toggle entre chat y panel de administración de configuración)

**Beneficios:**
- Reducir 8 props → 1 config array
- Conditional resource loading (mejor performance)
- Settings panel para user preferences
- Validación centralizada
- Documentación clara para developers

---

## 🔍 ESTADO ACTUAL DEL COMPONENTE

### Workspace.php (8 Props Individuales)

```php
// Archivo: src/View/Components/Chat/Workspace.php
public function __construct(
    ?LLMConversationSession $session = null,
    ?Collection $configurations = null,
    string $layout = 'bubble',                  // ❌ Prop 1
    bool $showMonitor = false,                  // ❌ Prop 2
    string $monitorLayout = 'drawer',           // ❌ Prop 3
    string $mode = 'full',                      // ❌ Prop 4
    bool $persist = true,                       // ❌ Prop 5
    bool $showToolbar = true                    // ❌ Prop 6
) {
    // ...
}
```

**Props adicionales NO en constructor:**
- `$session` (LLMConversationSession|null) - OK ✅
- `$configurations` (Collection) - OK ✅

### Invocación Actual (Quick Chat)

```blade
{{-- resources/views/admin/quick-chat/index.blade.php --}}
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :show-monitor="true"
    :monitor-open="true"
    monitor-layout="split-horizontal"
/>
```

### Conditional Loading Existente

```blade
{{-- resources/views/components/chat/chat-workspace.blade.php --}}
@if($monitorLayout === 'split-horizontal')
    @include('llm-manager::components.chat.partials.styles.split-horizontal')
@endif
```

**Patrón ya establecido:** ✅ Condicionales por feature/layout ya funcionando

---

## 🎯 DISEÑO DE CONFIGURACIÓN PROPUESTO

### 1. Estructura de Config Array

```php
// Configuración completa (todos los defaults documentados)
$config = [
    /**
     * Features - Enable/disable major features
     */
    'features' => [
        'monitor' => [
            'enabled' => true,              // Master toggle para todo el monitor
            'default_open' => true,         // Estado inicial (open/closed)
            'tabs' => [
                'console' => true,          // Tab Console (logs en tiempo real)
                'request_inspector' => true, // Tab Request Inspector (debugging)
                'activity_log' => true,     // Tab Activity Log (historial)
            ],
        ],
        'settings_panel' => true,           // Toggle Settings Panel (nuevo)
        'persistence' => true,              // Guardar mensajes en DB
        'toolbar' => true,                  // Mostrar toolbar con botones
    ],

    /**
     * UI Elements - Granular control de elementos visuales
     */
    'ui' => [
        'layout' => [
            'chat' => 'bubble',             // 'bubble', 'drawer', 'compact'
            'monitor' => 'split-horizontal', // 'drawer', 'tabs', 'split-horizontal', 'split-vertical', 'sidebar'
        ],
        'buttons' => [
            'new_chat' => true,             // Botón New Chat
            'clear' => true,                // Botón Clear Chat
            'settings' => true,             // Botón Settings (toggle panel)
            'download' => true,             // Botón Download History
            'monitor_toggle' => true,       // Botón Toggle Monitor
        ],
        'mode' => 'full',                   // 'full', 'demo', 'canvas-only'
    ],

    /**
     * Performance - Optimizaciones de carga
     */
    'performance' => [
        'lazy_load_tabs' => true,           // Cargar tabs solo cuando se activan
        'minify_assets' => false,           // Minificar JS/CSS (solo production)
        'cache_preferences' => true,        // Guardar en localStorage
    ],

    /**
     * Advanced - Opciones avanzadas
     */
    'advanced' => [
        'multi_instance' => false,          // Soporte múltiples chats en misma página
        'custom_css_class' => '',           // CSS class personalizada
        'debug_mode' => false,              // Logs detallados en console
    ],
];
```

### 2. Método de Validación

```php
// Archivo: src/Services/ChatWorkspaceConfigValidator.php
namespace Bithoven\LLMManager\Services;

class ChatWorkspaceConfigValidator
{
    /**
     * Defaults completos (documentados)
     */
    private static array $defaults = [
        'features' => [
            'monitor' => [
                'enabled' => true,
                'default_open' => true,
                'tabs' => [
                    'console' => true,
                    'request_inspector' => true,
                    'activity_log' => true,
                ],
            ],
            'settings_panel' => true,
            'persistence' => true,
            'toolbar' => true,
        ],
        'ui' => [
            'layout' => [
                'chat' => 'bubble',
                'monitor' => 'split-horizontal',
            ],
            'buttons' => [
                'new_chat' => true,
                'clear' => true,
                'settings' => true,
                'download' => true,
                'monitor_toggle' => true,
            ],
            'mode' => 'full',
        ],
        'performance' => [
            'lazy_load_tabs' => true,
            'minify_assets' => false,
            'cache_preferences' => true,
        ],
        'advanced' => [
            'multi_instance' => false,
            'custom_css_class' => '',
            'debug_mode' => false,
        ],
    ];

    /**
     * Reglas de validación
     */
    private static array $rules = [
        'features.monitor.enabled' => 'boolean',
        'features.monitor.default_open' => 'boolean',
        'features.monitor.tabs.console' => 'boolean',
        'features.monitor.tabs.request_inspector' => 'boolean',
        'features.monitor.tabs.activity_log' => 'boolean',
        'features.settings_panel' => 'boolean',
        'features.persistence' => 'boolean',
        'features.toolbar' => 'boolean',
        
        'ui.layout.chat' => 'in:bubble,drawer,compact',
        'ui.layout.monitor' => 'in:drawer,tabs,split-horizontal,split-vertical,sidebar',
        'ui.buttons.*' => 'boolean',
        'ui.mode' => 'in:full,demo,canvas-only',
        
        'performance.*' => 'boolean',
        
        'advanced.multi_instance' => 'boolean',
        'advanced.custom_css_class' => 'string|nullable',
        'advanced.debug_mode' => 'boolean',
    ];

    /**
     * Valida y mergea con defaults
     */
    public static function validate(array $config): array
    {
        // 1. Merge con defaults recursivamente
        $merged = array_replace_recursive(self::$defaults, $config);

        // 2. Validar tipos y valores
        $validator = \Validator::make($merged, self::$rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                'Invalid chat workspace configuration: ' . 
                $validator->errors()->first()
            );
        }

        // 3. Validaciones lógicas
        self::validateLogic($merged);

        return $merged;
    }

    /**
     * Validaciones lógicas (reglas complejas)
     */
    private static function validateLogic(array $config): void
    {
        // Si monitor disabled, todos los tabs deben estar disabled
        if (!$config['features']['monitor']['enabled']) {
            if ($config['features']['monitor']['tabs']['console'] ||
                $config['features']['monitor']['tabs']['request_inspector'] ||
                $config['features']['monitor']['tabs']['activity_log']) {
                throw new \InvalidArgumentException(
                    'Monitor tabs cannot be enabled when monitor feature is disabled'
                );
            }
        }

        // Si no hay toolbar, settings button no puede estar enabled
        if (!$config['features']['toolbar'] && 
            $config['ui']['buttons']['settings']) {
            throw new \InvalidArgumentException(
                'Settings button cannot be enabled when toolbar is disabled'
            );
        }
    }

    /**
     * Get default configuration
     */
    public static function defaults(): array
    {
        return self::$defaults;
    }
}
```

### 3. Workspace.php Refactorizado

```php
// Archivo: src/View/Components/Chat/Workspace.php
namespace Bithoven\LLMManager\View\Components\Chat;

use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

class Workspace extends Component
{
    public ?LLMConversationSession $session;
    public Collection $configurations;
    public array $config; // ✅ Nueva prop principal

    /**
     * Constructor con BACKWARD COMPATIBILITY
     */
    public function __construct(
        ?LLMConversationSession $session = null,
        ?Collection $configurations = null,
        
        // ===== NUEVO: Config array (prioridad 1) =====
        ?array $config = null,
        
        // ===== LEGACY: Props individuales (prioridad 2, deprecated) =====
        ?string $layout = null,
        ?bool $showMonitor = null,
        ?string $monitorLayout = null,
        ?string $mode = null,
        ?bool $persist = null,
        ?bool $showToolbar = null
    ) {
        $this->session = $session;
        $this->configurations = $configurations ?? LLMConfiguration::where('is_active', true)->get();

        // Si se pasa $config, usarlo (nueva forma)
        if ($config !== null) {
            $this->config = ChatWorkspaceConfigValidator::validate($config);
        }
        // Si no, construir config desde props legacy (backward compatibility)
        else {
            $legacyConfig = $this->buildConfigFromLegacyProps(
                $layout, $showMonitor, $monitorLayout, $mode, $persist, $showToolbar
            );
            $this->config = ChatWorkspaceConfigValidator::validate($legacyConfig);
        }
    }

    /**
     * Convertir props legacy a config array
     */
    private function buildConfigFromLegacyProps(
        ?string $layout,
        ?bool $showMonitor,
        ?string $monitorLayout,
        ?string $mode,
        ?bool $persist,
        ?bool $showToolbar
    ): array {
        $defaults = ChatWorkspaceConfigValidator::defaults();
        
        return [
            'features' => [
                'monitor' => [
                    'enabled' => $showMonitor ?? $defaults['features']['monitor']['enabled'],
                    'default_open' => $showMonitor ?? $defaults['features']['monitor']['default_open'],
                    'tabs' => $defaults['features']['monitor']['tabs'], // Todos enabled por defecto
                ],
                'persistence' => $persist ?? $defaults['features']['persistence'],
                'toolbar' => $showToolbar ?? $defaults['features']['toolbar'],
            ],
            'ui' => [
                'layout' => [
                    'chat' => $layout ?? $defaults['ui']['layout']['chat'],
                    'monitor' => $monitorLayout ?? $defaults['ui']['layout']['monitor'],
                ],
                'mode' => $mode ?? $defaults['ui']['mode'],
            ],
        ];
    }

    /**
     * Helper methods para acceder config en vistas
     */
    public function isMonitorEnabled(): bool
    {
        return $this->config['features']['monitor']['enabled'];
    }

    public function isMonitorTabEnabled(string $tab): bool
    {
        return $this->config['features']['monitor']['tabs'][$tab] ?? false;
    }

    public function isButtonEnabled(string $button): bool
    {
        return $this->config['ui']['buttons'][$button] ?? false;
    }

    public function getMonitorLayout(): string
    {
        return $this->config['ui']['layout']['monitor'];
    }

    public function getChatLayout(): string
    {
        return $this->config['ui']['layout']['chat'];
    }
}
```

---

## 🛠️ IMPLEMENTACIÓN

### FASE 1: Validator Class (2 horas)

**Archivos nuevos:**
- `src/Services/ChatWorkspaceConfigValidator.php` (300 líneas)
- `tests/Unit/Services/ChatWorkspaceConfigValidatorTest.php` (200 líneas)

**Tasks:**
1. ✅ Crear clase ChatWorkspaceConfigValidator
2. ✅ Definir array $defaults completo
3. ✅ Definir array $rules (Laravel validation)
4. ✅ Implementar método validate()
5. ✅ Implementar método validateLogic() (reglas complejas)
6. ✅ Unit tests (20 test cases)

### FASE 2: Workspace.php Refactor (3 horas)

**Archivos modificados:**
- `src/View/Components/Chat/Workspace.php` (180 líneas → 250 líneas)

**Tasks:**
1. ✅ Agregar prop $config (array)
2. ✅ Refactorizar constructor con backward compatibility
3. ✅ Implementar buildConfigFromLegacyProps()
4. ✅ Agregar helper methods (isMonitorEnabled, isMonitorTabEnabled, etc.)
5. ✅ Actualizar docblocks
6. ✅ Deprecation notices en props legacy

**Backward Compatibility:**
```php
// ✅ LEGACY (sigue funcionando)
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :show-monitor="true"
    monitor-layout="split-horizontal"
/>

// ✅ NUEVO (recomendado)
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :config="$chatConfig"
/>
```

### FASE 3: Conditional Resource Loading (3 horas)

**Archivos modificados:**
- `resources/views/components/chat/chat-workspace.blade.php` (66 líneas → 120 líneas)
- `resources/views/components/chat/layouts/split-horizontal-layout.blade.php` (180 líneas → 200 líneas)
- `resources/views/components/chat/layouts/sidebar-layout.blade.php` (si existe)

**Tasks:**
1. ✅ Blade directives para tabs del monitor
   ```blade
   @if($isMonitorTabEnabled('console'))
       @include('llm-manager::components.chat.shared.monitor-console')
   @endif
   
   @if($isMonitorTabEnabled('request_inspector'))
       @include('llm-manager::components.chat.shared.monitor-request-inspector')
   @endif
   ```

2. ✅ Conditional scripts loading
   ```blade
   @if($isMonitorTabEnabled('request_inspector'))
       @include('llm-manager::components.chat.partials.scripts.request-inspector')
   @endif
   ```

3. ✅ Conditional styles loading
   ```blade
   @if($getMonitorLayout() === 'split-horizontal')
       @include('llm-manager::components.chat.partials.styles.split-horizontal')
   @endif
   ```

4. ✅ Conditional buttons
   ```blade
   @if($isButtonEnabled('settings'))
       <button type="button" class="btn btn-sm btn-icon">
           {!! getIcon('ki-setting-2', 'fs-2x', '', 'i') !!}
       </button>
   @endif
   ```

**Performance Benchmark:**
- **ANTES:** Carga 100% de scripts/styles (100KB JS + 50KB CSS)
- **DESPUÉS:** Carga condicional (50-70KB JS + 25-35KB CSS)
- **Ahorro:** 30-50% bundle size reduction

### FASE 4: Settings Panel UI (4 horas)

**Archivos nuevos:**
- `resources/views/components/chat/partials/settings-panel.blade.php` (250 líneas)
- `resources/js/custom/chat-settings-panel.js` (200 líneas) - Alpine component

**Tasks:**
1. ✅ Crear Settings Panel UI (reemplaza chat content cuando activo)
2. ✅ Toggle button en header (ya existe en split-horizontal-layout lines 26-35)
3. ✅ Alpine.js component para state management
   ```javascript
   Alpine.data('chatSettings', (sessionId) => ({
       panel_open: false,
       config: {...}, // Config actual
       
       togglePanel() {
           this.panel_open = !this.panel_open;
       },
       
       saveConfig() {
           // Guardar en localStorage + emit event
           localStorage.setItem(`llm_chat_config_${sessionId}`, JSON.stringify(this.config));
           this.$dispatch('config-updated', this.config);
       }
   }));
   ```

4. ✅ Secciones del panel:
   - **Monitor Settings:** Enable/disable tabs individuales
   - **UI Preferences:** Layout, buttons, mode
   - **Performance:** Lazy loading, cache preferences
   - **Advanced:** Debug mode, custom CSS class

5. ✅ Save/Reset buttons
6. ✅ localStorage persistence
7. ✅ Custom events (config-updated)

**UI Mockup:**
```blade
{{-- Settings Panel (toggle replaces chat) --}}
<div x-show="panel_open" class="settings-panel p-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chat Settings</h3>
        </div>
        <div class="card-body">
            {{-- Monitor Settings --}}
            <div class="mb-5">
                <h5>Monitor</h5>
                <div class="form-check form-switch">
                    <input type="checkbox" x-model="config.features.monitor.enabled">
                    <label>Enable Monitor</label>
                </div>
                <div class="form-check form-switch" x-show="config.features.monitor.enabled">
                    <input type="checkbox" x-model="config.features.monitor.tabs.console">
                    <label>Console Tab</label>
                </div>
                {{-- ... más tabs --}}
            </div>

            {{-- UI Preferences --}}
            <div class="mb-5">
                <h5>UI Preferences</h5>
                <select x-model="config.ui.layout.monitor">
                    <option value="split-horizontal">Split Horizontal</option>
                    <option value="sidebar">Sidebar</option>
                    <option value="drawer">Drawer</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="mb-5">
                <h5>Buttons</h5>
                <div class="row">
                    <div class="col-6">
                        <input type="checkbox" x-model="config.ui.buttons.new_chat">
                        <label>New Chat</label>
                    </div>
                    {{-- ... más botones --}}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button @click="saveConfig()" class="btn btn-primary">Save Settings</button>
            <button @click="resetConfig()" class="btn btn-light">Reset to Defaults</button>
        </div>
    </div>
</div>
```

### FASE 5: Documentation (2 horas)

**Archivos nuevos/modificados:**
- `docs/components/CHAT-WORKSPACE-CONFIG.md` (400 líneas) - Guía de configuración
- `docs/components/CHAT-WORKSPACE.md` (actualizar con nueva sección)
- `README.md` (actualizar Quick Start)

**Secciones del doc:**
1. **Configuration Overview** - Estructura completa del config array
2. **Configuration Reference** - Todas las opciones documentadas
3. **Usage Examples** - 10 ejemplos comunes
4. **Migration Guide** - Legacy props → Config array
5. **Best Practices** - Recomendaciones
6. **Performance Tips** - Optimizaciones
7. **Troubleshooting** - Errores comunes

### FASE 6: Testing (2 horas)

**Archivos nuevos:**
- `tests/Unit/Services/ChatWorkspaceConfigValidatorTest.php` (200 líneas)
- `tests/Feature/Components/ChatWorkspaceConfigTest.php` (150 líneas)
- `tests/Browser/ChatSettingsPanelTest.php` (100 líneas) - Dusk test

**Test Cases:**
1. **Unit Tests (20 tests):**
   - Defaults loading
   - Config validation (valid/invalid)
   - Merge behavior
   - Logic validation (monitor disabled → tabs disabled)
   - Edge cases

2. **Feature Tests (15 tests):**
   - Backward compatibility (legacy props)
   - Config array priority
   - Helper methods (isMonitorEnabled, etc.)
   - Conditional rendering

3. **Browser Tests (10 tests):**
   - Settings panel toggle
   - Config save/load
   - Custom events emission
   - LocalStorage persistence

---

## 📊 CASOS DE USO

### Caso 1: Quick Chat (Monitor Full)
```php
// Controller
$chatConfig = [
    'features' => [
        'monitor' => [
            'enabled' => true,
            'default_open' => true,
            'tabs' => [
                'console' => true,
                'request_inspector' => true,
                'activity_log' => true,
            ],
        ],
    ],
    'ui' => [
        'layout' => [
            'monitor' => 'split-horizontal',
        ],
    ],
];

// View
<x-llm-manager-chat-workspace
    :session="$session"
    :configurations="$configurations"
    :config="$chatConfig"
/>
```

### Caso 2: Conversations (Solo Console)
```php
$chatConfig = [
    'features' => [
        'monitor' => [
            'enabled' => true,
            'tabs' => [
                'console' => true,
                'request_inspector' => false, // ❌ Disabled
                'activity_log' => false,      // ❌ Disabled
            ],
        ],
    ],
    'ui' => [
        'layout' => [
            'monitor' => 'sidebar',
        ],
    ],
];
```

### Caso 3: Embedded Chat (Sin Monitor)
```php
$chatConfig = [
    'features' => [
        'monitor' => [
            'enabled' => false, // ❌ Completamente disabled
        ],
        'toolbar' => false,     // ❌ Sin toolbar
    ],
    'ui' => [
        'mode' => 'canvas-only', // Solo el chat
    ],
];
```

### Caso 4: Developer Mode (Todo Enabled)
```php
$chatConfig = ChatWorkspaceConfigValidator::defaults(); // Usar defaults

// O customizar:
$chatConfig = array_merge(ChatWorkspaceConfigValidator::defaults(), [
    'advanced' => [
        'debug_mode' => true, // ✅ Console logs detallados
    ],
]);
```

---

## 🎯 CRITERIOS DE ACEPTACIÓN

### Funcionalidad
- [ ] Config array valida correctamente (tipos, valores, lógica)
- [ ] Backward compatibility 100% (legacy props siguen funcionando)
- [ ] Conditional resource loading (solo carga features enabled)
- [ ] Settings panel funcional (save/load desde localStorage)
- [ ] Custom events emitidos correctamente

### Performance
- [ ] Bundle size reduction 30-50% cuando tabs disabled
- [ ] Lazy loading de tabs funcional
- [ ] Sin degradación en carga inicial (< 50ms overhead)

### Testing
- [ ] Unit tests 100% coverage en ConfigValidator
- [ ] Feature tests para backward compatibility
- [ ] Browser tests para Settings panel

### Documentación
- [ ] Config reference completa
- [ ] Migration guide clara
- [ ] 10 ejemplos de uso
- [ ] Troubleshooting guide

---

## 📅 CRONOGRAMA

**Estimación Total:** 12-15 horas

| Fase | Duración | Prioridad | Dependencias |
|------|----------|-----------|--------------|
| FASE 1: Validator Class | 2 horas | ALTA | Ninguna |
| FASE 2: Workspace.php Refactor | 3 horas | ALTA | FASE 1 |
| FASE 3: Conditional Loading | 3 horas | MEDIA | FASE 2 |
| FASE 4: Settings Panel UI | 4 horas | BAJA | FASE 2 |
| FASE 5: Documentation | 2 horas | MEDIA | FASE 1-4 |
| FASE 6: Testing | 2 horas | ALTA | FASE 1-4 |

**Fases Críticas (path bloqueante):**
1. FASE 1 → FASE 2 → FASE 6 (Core functionality + testing)

**Fases Opcionales (pueden omitirse):**
- FASE 4: Settings Panel UI (puede implementarse después)

---

## 🚨 RIESGOS Y MITIGACIÓN

### Riesgo 1: Breaking Changes en Legacy Props
**Probabilidad:** MEDIA  
**Impacto:** ALTO  
**Mitigación:**
- Unit tests extensivos para backward compatibility
- Mantener props legacy funcionales (no deprecate todavía)
- Documentar migración gradual

### Riesgo 2: Performance Overhead en Validación
**Probabilidad:** BAJA  
**Impacto:** MEDIO  
**Mitigación:**
- Validar solo en constructor (1 vez por request)
- Cache results en property $config
- No validar en cada helper method

### Riesgo 3: Settings Panel State Management Complejo
**Probabilidad:** MEDIA  
**Impacidad:** MEDIO  
**Mitigación:**
- Usar Alpine.js (ya presente en stack)
- localStorage como single source of truth
- Custom events para comunicación externa

---

## 📖 REFERENCIAS

**Archivos Clave:**
- `src/View/Components/Chat/Workspace.php` (componente actual)
- `resources/views/components/chat/chat-workspace.blade.php` (template principal)
- `resources/views/components/chat/layouts/split-horizontal-layout.blade.php` (layout con Settings buttons)
- `docs/components/CHAT-WORKSPACE.md` (documentación actual)

**Documentación Relacionada:**
- [PLAN-v1.0.7.md](./PLAN-v1.0.7.md) - Plan principal
- [CHANGELOG.md](../CHANGELOG.md) - Historial de cambios
- Laravel Blade Components: https://laravel.com/docs/11.x/blade#components
- Alpine.js Documentation: https://alpinejs.dev/

---

## ✅ CHECKLIST PRE-IMPLEMENTACIÓN

**ANTES de empezar, verificar:**
- [ ] Leer COMPLETO este plan
- [ ] Leer [PLAN-v1.0.7.md](./PLAN-v1.0.7.md) Lesson #16 (análisis arquitectural)
- [ ] Analizar `Workspace.php` completo (180 líneas)
- [ ] Analizar invocaciones actuales del componente
- [ ] Revisar docs/components/CHAT-WORKSPACE.md
- [ ] Verificar no hay regresiones en Quick Chat actual

**Durante implementación:**
- [ ] Commits atómicos por fase
- [ ] Unit tests ANTES de feature tests
- [ ] Documentar cada config option en docblocks
- [ ] Validar backward compatibility en cada commit

**Después de implementación:**
- [ ] Run full test suite (`php artisan test`)
- [ ] Manual testing en Quick Chat
- [ ] Verificar bundle size reduction
- [ ] Update PLAN-v1.0.7.md progress
- [ ] Update CHANGELOG.md

---

**Autor:** Claude (Claude Sonnet 4.5, Anthropic)  
**Fecha:** 9 de diciembre de 2025, 09:00
