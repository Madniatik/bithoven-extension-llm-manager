# Testing

Suite de tests para el Chat Workspace Configuration System.

---

## Unit Tests

### Ejecutar Tests de Validación

```bash
php vendor/bin/phpunit tests/Unit/Services/ChatWorkspaceConfigValidatorTest.php
```

### Test Cases (13/13 passing)

- Empty config returns defaults
- Valid config passes
- Partial config merges with defaults
- Invalid layouts throw exception
- Logical rules validated

---

## Feature Tests

### Ejecutar Tests de Componentes

```bash
php vendor/bin/phpunit tests/Feature/Components/ChatWorkspaceConfigTest.php
```

### Test Cases (14/14 passing)

- Component accepts config array
- Backward compatibility with legacy props
- Config priority over legacy props
- Helper methods work correctly
- Conditional rendering

---

## Todos los Tests

```bash
# Ejecutar ambos (Unit + Feature)
php vendor/bin/phpunit \
    tests/Unit/Services/ChatWorkspaceConfigValidatorTest.php \
    tests/Feature/Components/ChatWorkspaceConfigTest.php
```

**Resultado esperado:** 27/27 tests passing ✅

---

## Manual Testing

### Checklist

- [ ] Monitor tabs load correctly
- [ ] Settings panel saves to DB
- [ ] Legacy props still work
- [ ] Config array has priority
- [ ] Validation catches errors
- [ ] Performance optimizations work
- [ ] Lazy loading functional
- [ ] localStorage cache works

---

**Documentación Verificada:** `docs/components/CHAT-WORKSPACE-CONFIG.md.archived`