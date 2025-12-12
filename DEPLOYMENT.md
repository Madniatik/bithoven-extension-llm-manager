# LLM Manager Extension - Deployment Guide

**Version:** v0.4.0-dev  
**Last Updated:** 12 de diciembre de 2025

## 📦 Quick Deployment

### 1. Update Extension in CPANEL

```bash
cd /path/to/CPANEL
composer update bithoven/llm-manager
```

### 2. Publish Assets (CRITICAL)

```bash
php artisan vendor:publish --tag=llm-assets --force
```

**What this does:**
- Copies `extension/public/` → `cpanel/public/vendor/bithoven/llm-manager/`
- Includes Monitor JS modules (core/actions/ui)
- Required for browser to load JavaScript modules

### 3. Run Migrations (if needed)

```bash
php artisan migrate
```

### 4. Clear Cache

```bash
php artisan optimize:clear
```

---

## 🔍 Verification

### Check Assets Published

```bash
# Should list monitor JS structure
ls -la public/vendor/bithoven/llm-manager/js/monitor/

# Expected output:
# actions/
# core/
# ui/
# monitor.js
```

### Test HTTP Access

```bash
# Should return JavaScript code (not 404)
curl http://localhost:8000/vendor/bithoven/llm-manager/js/monitor/core/MonitorStorage.js
```

### Browser Console

Open Quick Chat and check browser console:
- ✅ NO 404 errors for JS modules
- ✅ `window.LLMMonitorFactory` exists
- ✅ Monitor initialized with "Monitor ready" log

---

## 🚀 First-Time Installation

### From GitHub Release

```bash
cd /path/to/CPANEL

# Install extension
composer require bithoven/llm-manager

# Publish everything
php artisan vendor:publish --provider="Bithoven\LLMManager\LLMServiceProvider"

# OR publish selectively
php artisan vendor:publish --tag=llm-config
php artisan vendor:publish --tag=llm-views
php artisan vendor:publish --tag=llm-lang
php artisan vendor:publish --tag=llm-assets

# Run migrations
php artisan migrate

# Seed permissions
php artisan db:seed --class=LLMPermissionsSeeder
```

### From Local Development

```bash
# 1. In extension directory
cd /path/to/bithoven-extension-llm-manager
./scripts/copy-monitor-js.sh  # Copy resources/js → public/js

# 2. Push to GitHub
git push origin main

# 3. In CPANEL
cd /path/to/CPANEL
composer update bithoven/llm-manager
php artisan vendor:publish --tag=llm-assets --force
```

---

## 🔄 Update Workflow

### When Monitor JS Changes

```bash
# 1. Edit source files
vim resources/js/monitor/actions/copy.js

# 2. Copy to public (auto-deploy script)
./scripts/copy-monitor-js.sh

# 3. Commit changes
git add .
git commit -m "feat(monitor): improve copy action"
git push

# 4. Update in CPANEL
cd /path/to/CPANEL
composer update bithoven/llm-manager
php artisan vendor:publish --tag=llm-assets --force  # ← CRITICAL
```

**⚠️ IMPORTANT:** Always run `vendor:publish --tag=llm-assets --force` after updating extension if JS changed.

### When Blade Views Change

```bash
# Option 1: Publish views (use vendor views)
php artisan vendor:publish --tag=llm-views --force

# Option 2: Use package views directly (recommended for dev)
# Views auto-loaded from vendor/bithoven/llm-manager/resources/views/
# No publish needed
```

### When Config Changes

```bash
php artisan vendor:publish --tag=llm-config --force
php artisan config:cache
```

---

## 📁 Published Paths

### Assets (Tag: llm-assets)

```
Source: vendor/bithoven/llm-manager/public/
Target: public/vendor/bithoven/llm-manager/

Files:
  js/monitor/core/MonitorFactory.js
  js/monitor/core/MonitorInstance.js
  js/monitor/core/MonitorStorage.js
  js/monitor/actions/clear.js
  js/monitor/actions/copy.js
  js/monitor/actions/download.js
  js/monitor/ui/render.js
  js/monitor/monitor.js
```

### Views (Tag: llm-views)

```
Source: vendor/bithoven/llm-manager/resources/views/
Target: resources/views/vendor/llm-manager/

Note: Usually NOT published (loaded from package)
Publish only if you need to customize views
```

### Config (Tag: llm-config)

```
Source: vendor/bithoven/llm-manager/config/llm-manager.php
Target: config/llm-manager.php

Publish if you need to customize settings
```

---

## 🐛 Troubleshooting

### Issue: 404 on JS modules

```
GET http://localhost:8000/vendor/bithoven/llm-manager/js/monitor/core/MonitorStorage.js
→ 404 Not Found
```

**Solution:**
```bash
php artisan vendor:publish --tag=llm-assets --force
```

### Issue: Monitor not initializing

**Check 1:** Browser console errors?
```javascript
// Should exist
console.log(window.LLMMonitorFactory);
```

**Check 2:** Assets published?
```bash
ls -la public/vendor/bithoven/llm-manager/js/monitor/
```

**Check 3:** Correct path in Blade?
```blade
{{-- monitor-api.blade.php --}}
const basePath = '/vendor/bithoven/llm-manager/js/monitor'; ✅
const basePath = '/vendor/llm-manager/js/monitor';          ❌
```

### Issue: Changes not reflecting

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Force republish
php artisan vendor:publish --tag=llm-assets --force

# 3. Hard refresh browser (Cmd+Shift+R / Ctrl+Shift+F5)
```

### Issue: Old version still loading

```bash
# Check composer version
composer show bithoven/llm-manager

# Force update
composer update bithoven/llm-manager --with-dependencies

# Republish
php artisan vendor:publish --tag=llm-assets --force
```

---

## 🔐 Production Deployment

### Pre-Deploy Checklist

- [ ] All tests passing
- [ ] Monitor JS copied (`./scripts/copy-monitor-js.sh`)
- [ ] Version bumped in `extension.json`
- [ ] CHANGELOG.md updated
- [ ] Git tagged with version
- [ ] Pushed to GitHub

### Deploy Steps

```bash
# 1. SSH to production server
ssh user@production-server

# 2. Backup database
php artisan backup:database

# 3. Enable maintenance mode
php artisan down --message="Updating LLM Manager Extension" --retry=60

# 4. Update extension
composer update bithoven/llm-manager

# 5. Publish assets
php artisan vendor:publish --tag=llm-assets --force

# 6. Run migrations
php artisan migrate --force

# 7. Clear all caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Disable maintenance mode
php artisan up

# 9. Verify
curl https://production.com/vendor/bithoven/llm-manager/js/monitor/core/MonitorStorage.js
```

### Rollback Plan

```bash
# 1. Restore from backup
php artisan backup:restore

# 2. Downgrade extension
composer require bithoven/llm-manager:0.2.2

# 3. Republish old assets
php artisan vendor:publish --tag=llm-assets --force

# 4. Clear caches
php artisan optimize:clear
```

---

## 📊 Asset Publishing Tags

| Tag | Command | When to Use |
|-----|---------|-------------|
| `llm-assets` | `--tag=llm-assets` | **After every update** (JS/CSS changes) |
| `llm-config` | `--tag=llm-config` | Config customization needed |
| `llm-views` | `--tag=llm-views` | View customization needed (rare) |
| `llm-lang` | `--tag=llm-lang` | Translation customization needed |
| All | `--provider="Bithoven\LLMManager\LLMServiceProvider"` | First-time install |

---

## 🎯 Quick Reference

```bash
# Most common workflow (after extension update)
composer update bithoven/llm-manager && \
php artisan vendor:publish --tag=llm-assets --force && \
php artisan optimize:clear

# Development workflow (local changes)
cd extension/
./scripts/copy-monitor-js.sh && \
git commit -am "feat: update monitor" && \
git push && \
cd ../CPANEL/ && \
composer update bithoven/llm-manager && \
php artisan vendor:publish --tag=llm-assets --force

# Verify assets
curl http://localhost:8000/vendor/bithoven/llm-manager/js/monitor/core/MonitorStorage.js | head -10
```

---

**Version:** v0.4.0-dev (33% complete)  
**Last Updated:** 12 de diciembre de 2025  
**Critical Note:** Always publish assets after composer update!  
**Latest Feature:** Service Layer + Provider Repositories (FASE 1-2 complete)
