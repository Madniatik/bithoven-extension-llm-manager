# Performance Tips

Optimizaciones de carga y rendimiento.

---

## 1. Conditional Resource Loading

El sistema carga recursos (JS/CSS) solo para features habilitadas:

| Configuración | Bundle Size | Reducción |
|---------------|-------------|-----------|
| **ALL ENABLED** | 119 KB | 0% (baseline) |
| **Monitor (1 tab)** | 102 KB | -15% |
| **No Monitor** | 85 KB | -29% |
| **Minimal** | 74 KB | -39% |

### Benchmark Script

```bash
./scripts/benchmark-conditional-loading.sh
```

---

## 2. Lazy Loading de Tabs

```php
$config = [
    'performance' => [
        'lazy_load_tabs' => true,  // Tabs se cargan al activarse
    ],
];
```

**Beneficio:** Primera carga más rápida (~200-300ms menos).

---

## 3. Cache de Preferencias

```php
$config = [
    'performance' => [
        'cache_preferences' => true,  // localStorage cache
    ],
];
```

**Beneficio:** Settings Panel carga instantáneamente (sin DB query).

---

## 4. Minificación (Production)

```php
$config = [
    'performance' => [
        'minify_assets' => app()->environment('production'),
    ],
];
```

**Beneficio:** Bundle size reducido ~40%.

---

## 5. Disable Unused Features

```php
// ✅ GOOD: No carga JS/CSS
$config = [
    'features' => [
        'monitor' => ['enabled' => false],
    ],
];

// ❌ BAD: Cargar y ocultar con CSS
$config = [
    'features' => [
        'monitor' => ['enabled' => true],
    ],
];
// Y luego: <div style="display:none">...</div>
```

---

## Tabla de Optimizaciones

| Optimización | Reducción | Tiempo Ahorrado |
|--------------|-----------|-----------------|
| Lazy Load Tabs | -15% | ~200ms |
| Minify Assets | -40% | ~500ms |
| Cache Preferences | 0% | ~100ms (DB query) |
| Disable Unused Features | -30% | ~300ms |

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`