# Unit Tests - LLM Manager Extension

## 📋 Test Coverage

### Models (4 test files)
- ✅ `LLMConfigurationTest` - 8 tests
- ✅ `LLMPromptTemplateTest` - 7 tests
- ✅ `LLMConversationSessionTest` - 8 tests
- ✅ `LLMToolDefinitionTest` - 7 tests

### Services (3 test files)
- ✅ `LLMManagerTest` - 8 tests
- ✅ `LLMBudgetManagerTest` - 6 tests
- ✅ `LLMPromptServiceTest` - 7 tests

**Total: 51 Unit Tests**

---

## 🚀 Running Tests

### All Tests
```bash
vendor/bin/phpunit
```

### Specific Test Suite
```bash
# Only Unit tests
vendor/bin/phpunit --testsuite Unit

# Only Feature tests (after installation)
vendor/bin/phpunit --testsuite Feature
```

### Specific Test File
```bash
vendor/bin/phpunit tests/Unit/Models/LLMConfigurationTest.php
```

### With Coverage
```bash
vendor/bin/phpunit --coverage-html tests/coverage
```

---

## 📦 Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Run Tests
```bash
vendor/bin/phpunit
```

---

## 🧪 Test Structure

```
tests/
├── TestCase.php              # Base test case with Orchestra setup
├── Unit/
│   ├── Models/               # Model tests
│   │   ├── LLMConfigurationTest.php
│   │   ├── LLMPromptTemplateTest.php
│   │   ├── LLMConversationSessionTest.php
│   │   └── LLMToolDefinitionTest.php
│   └── Services/             # Service tests
│       ├── LLMManagerTest.php
│       ├── LLMBudgetManagerTest.php
│       └── LLMPromptServiceTest.php
└── Feature/                  # Feature tests (require installation)
    └── (to be created after installation)
```

---

## ✅ Test Checklist

### Models
- [x] LLMConfiguration - CRUD, scopes, relationships, encryption
- [x] LLMPromptTemplate - Interpolation, variables, scopes
- [x] LLMConversationSession - Messages, tokens, cost calculation
- [x] LLMToolDefinition - Parameters, validation, function calling format
- [ ] LLMUsageLog
- [ ] LLMDocumentKnowledgeBase
- [ ] LLMMCPConnector

### Services
- [x] LLMManager - Configuration management, provider resolution
- [x] LLMBudgetManager - Budget tracking, alerts, spending analysis
- [x] LLMPromptService - Template rendering, variable validation
- [ ] LLMExecutor
- [ ] LLMConversationManager
- [ ] LLMRAGService
- [ ] LLMToolService

### Providers
- [ ] OpenAIProvider
- [ ] AnthropicProvider
- [ ] OllamaProvider
- [ ] CustomProvider

---

## 📝 Notes

- Tests use **SQLite in-memory database** for speed
- Tests are **isolated** (RefreshDatabase trait)
- No real API calls (mocking required for Provider tests)
- Orchestra Testbench provides Laravel environment

---

## 🎯 Next Steps

1. ✅ Create remaining Model tests
2. ✅ Create remaining Service tests
3. ✅ Create Provider tests (with mocking)
4. 🔄 Install extension in CPANEL
5. 🔄 Create Feature tests (Controllers, Routes, Middleware)
6. 🔄 Create Integration tests (End-to-end workflows)
