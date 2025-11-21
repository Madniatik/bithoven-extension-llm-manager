# 🤝 Contributing to LLM Manager

**Welcome!** Thank you for considering contributing to LLM Manager. This document provides guidelines and instructions for contributing to the project.

---

## 📑 Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Setup](#development-setup)
4. [Contribution Workflow](#contribution-workflow)
5. [Coding Standards](#coding-standards)
6. [Testing Requirements](#testing-requirements)
7. [Documentation](#documentation)
8. [Pull Request Process](#pull-request-process)

---

## Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Examples of behavior that contributes to a positive environment:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

**Examples of unacceptable behavior:**
- The use of sexualized language or imagery
- Trolling, insulting/derogatory comments, and personal attacks
- Public or private harassment
- Publishing others' private information without permission
- Other conduct which could reasonably be considered inappropriate

---

## Getting Started

### Prerequisites

- **PHP:** 8.2+
- **Laravel:** 11.x
- **Node.js:** 18+
- **Composer:** 2.x
- **Git:** 2.x

### Areas to Contribute

1. **Bug Fixes** - Fix reported issues
2. **New Features** - Implement requested features
3. **Documentation** - Improve or translate docs
4. **Testing** - Add test coverage
5. **Performance** - Optimize code
6. **Code Review** - Review PRs

---

## Development Setup

### 1. Fork and Clone

```bash
# Fork repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR_USERNAME/bithoven-extension-llm-manager.git
cd bithoven-extension-llm-manager
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies (for MCP servers)
npm install

# Python dependencies (optional, for some MCP servers)
pip install -r requirements.txt
```

### 3. Configure Environment

```bash
# Copy example environment
cp .env.example .env

# Configure your test database
DB_CONNECTION=sqlite
DB_DATABASE=database/testing.sqlite

# Add test API keys (optional)
OPENAI_API_KEY=sk-test-...
ANTHROPIC_API_KEY=sk-ant-test-...
```

### 4. Run Migrations

```bash
php artisan migrate
php artisan db:seed --class=LLMTestSeeder
```

### 5. Run Tests

```bash
# PHPUnit tests
vendor/bin/phpunit

# Specific test
vendor/bin/phpunit --filter PromptTemplateTest

# With coverage
vendor/bin/phpunit --coverage-html coverage
```

---

## Contribution Workflow

### 1. Create a Branch

```bash
# Feature branch
git checkout -b feature/add-streaming-support

# Bug fix branch
git checkout -b fix/provider-connection-timeout

# Documentation branch
git checkout -b docs/improve-installation-guide
```

**Branch naming convention:**
- `feature/` - New features
- `fix/` - Bug fixes
- `docs/` - Documentation
- `refactor/` - Code refactoring
- `test/` - Test improvements

### 2. Make Changes

- Write clean, readable code
- Follow PSR-12 coding standards
- Add tests for new features
- Update documentation

### 3. Commit Changes

```bash
git add .
git commit -m "feat: add streaming support for OpenAI provider"
```

**Commit message format:**
```
<type>: <subject>

<body>

<footer>
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation
- `style` - Formatting, missing semicolons, etc.
- `refactor` - Code restructuring
- `test` - Adding tests
- `chore` - Updating build tasks, etc.

**Examples:**
```bash
feat: add streaming support for OpenAI provider

Implement Server-Sent Events (SSE) streaming for real-time
response generation. Includes new StreamingResponse class
and tests.

Closes #123
```

```bash
fix: resolve provider connection timeout

Increase default timeout from 30s to 60s and add retry logic
for failed connections. Fixes issue with large responses.

Fixes #456
```

### 4. Run Quality Checks

```bash
# Run tests
vendor/bin/phpunit

# PHP CS Fixer (code style)
vendor/bin/php-cs-fixer fix

# PHPStan (static analysis)
vendor/bin/phpstan analyse

# Run all checks
composer test
```

### 5. Push to GitHub

```bash
git push origin feature/add-streaming-support
```

### 6. Create Pull Request

1. Go to GitHub repository
2. Click **"New Pull Request"**
3. Select your branch
4. Fill in PR template
5. Submit for review

---

## Coding Standards

### PSR-12

We follow PSR-12 coding standards. Use PHP CS Fixer:

```bash
vendor/bin/php-cs-fixer fix
```

### Code Style

**Classes:**
```php
<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\Log;

class MyService
{
    public function __construct(
        private readonly Repository $repository
    ) {}
    
    public function process(string $input): array
    {
        // Method implementation
    }
}
```

**Methods:**
- Use type hints for parameters and return types
- Keep methods focused (single responsibility)
- Maximum 50 lines per method (ideally < 20)

**Naming:**
- Classes: `PascalCase`
- Methods: `camelCase`
- Variables: `camelCase`
- Constants: `UPPER_SNAKE_CASE`

### Documentation

**PHPDoc blocks:**
```php
/**
 * Generate LLM response using specified provider.
 *
 * @param string $prompt The input prompt
 * @param array $context Optional conversation context
 * @return array Response with content, usage, and cost
 * @throws LLMException If generation fails
 */
public function generate(string $prompt, array $context = []): array
{
    // Implementation
}
```

---

## Testing Requirements

### Test Coverage

All new features must include tests. Aim for:
- **Unit Tests:** 80%+ coverage
- **Feature Tests:** All major features
- **Integration Tests:** Provider integrations

### Writing Tests

**Unit Test Example:**
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Bithoven\LLMManager\Services\PromptService;

class PromptServiceTest extends TestCase
{
    public function test_renders_template_with_variables(): void
    {
        $service = app(PromptService::class);
        
        $rendered = $service->render('greeting', [
            'name' => 'Alice',
        ]);
        
        $this->assertStringContainsString('Alice', $rendered);
    }
    
    public function test_throws_exception_for_missing_variables(): void
    {
        $this->expectException(MissingVariablesException::class);
        
        $service = app(PromptService::class);
        $service->render('greeting', []);
    }
}
```

**Feature Test Example:**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Bithoven\LLMManager\Facades\LLM;

class LLMFacadeTest extends TestCase
{
    public function test_generates_response_with_openai(): void
    {
        $response = LLM::provider('openai')
            ->model('gpt-4o-mini')
            ->generate('Say hello');
        
        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('usage', $response);
        $this->assertIsString($response['content']);
    }
}
```

### Running Tests

```bash
# All tests
vendor/bin/phpunit

# Specific test
vendor/bin/phpunit --filter PromptServiceTest

# With coverage
vendor/bin/phpunit --coverage-html coverage

# Watch mode
vendor/bin/phpunit-watcher watch
```

---

## Documentation

### Update Documentation

When adding features, update:

1. **README.md** - If feature changes setup/usage
2. **USAGE-GUIDE.md** - Add usage examples
3. **API-REFERENCE.md** - Document new methods
4. **EXAMPLES.md** - Add code examples
5. **CHANGELOG.md** - Log changes

### Documentation Style

**Clear headings:**
```markdown
### Example: Creating a Prompt Template

**Create template:**

1. Navigate to `/admin/llm/prompts`
2. Click "Create Template"
3. Fill in the form
```

**Code examples:**
```markdown
```php
use Bithoven\LLMManager\Facades\LLM;

$response = LLM::generate('Hello!');
echo $response['content'];
```
```

**Screenshots:**
- Use PNG format
- Optimize size (<200KB)
- Store in `docs/images/`

---

## Pull Request Process

### Before Submitting

✅ **Checklist:**
- [ ] Tests pass (`vendor/bin/phpunit`)
- [ ] Code style is correct (`php-cs-fixer fix`)
- [ ] Static analysis passes (`phpstan analyse`)
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] No merge conflicts with `main`
- [ ] Commit messages follow convention

### PR Template

```markdown
## Description

Brief description of what this PR does.

## Type of Change

- [ ] Bug fix (non-breaking change)
- [ ] New feature (non-breaking change)
- [ ] Breaking change (fix/feature that would break existing functionality)
- [ ] Documentation update

## Testing

Describe how you tested this change.

## Screenshots (if applicable)

Add screenshots for UI changes.

## Checklist

- [ ] Tests pass
- [ ] Code follows style guidelines
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

### Review Process

1. **Automated Checks:**
   - GitHub Actions runs tests
   - Code coverage is checked
   - Style checks run

2. **Manual Review:**
   - Maintainer reviews code
   - Feedback provided
   - Discussion if needed

3. **Approval:**
   - At least 1 approval required
   - All checks must pass
   - No merge conflicts

4. **Merge:**
   - Squash and merge (default)
   - Maintainer merges PR
   - Branch is deleted

---

## Issue Reporting

### Bug Reports

**Template:**
```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
- OS: [e.g., macOS 14.0]
- PHP: [e.g., 8.2.12]
- Laravel: [e.g., 11.0]
- LLM Manager: [e.g., 1.0.0]

**Additional context**
Any other context about the problem.
```

### Feature Requests

**Template:**
```markdown
**Is your feature request related to a problem?**
A clear description of the problem.

**Describe the solution**
A clear description of what you want to happen.

**Describe alternatives**
Alternative solutions you've considered.

**Additional context**
Any other context or screenshots.
```

---

## Recognition

Contributors are recognized in:
- `CONTRIBUTORS.md` file
- GitHub contributors page
- Release notes

**Thank you for contributing!** 🎉

---

## Questions?

- **Email:** support@bithoven.com
- **Discord:** https://discord.gg/bithoven
- **GitHub Discussions:** https://github.com/bithoven/llm-manager/discussions

---

**Happy contributing!** 🚀
