#!/bin/bash
#
# Copy Monitor JavaScript Modules
# Copies modular JS from resources/ to public/ for web access
#
# Usage: ./scripts/copy-monitor-js.sh

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

SOURCE_DIR="$PROJECT_ROOT/resources/js/monitor"
TARGET_DIR="$PROJECT_ROOT/public/js/monitor"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📦 Copy Monitor JavaScript Modules"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    echo "❌ ERROR: Source directory not found: $SOURCE_DIR"
    exit 1
fi

# Create target directories
echo "📁 Creating target directories..."
mkdir -p "$TARGET_DIR/core"
mkdir -p "$TARGET_DIR/actions"
mkdir -p "$TARGET_DIR/ui"

# Copy files
echo "📋 Copying files..."

# Core modules
if [ -d "$SOURCE_DIR/core" ]; then
    cp "$SOURCE_DIR/core/"*.js "$TARGET_DIR/core/" 2>/dev/null || true
    echo "   ✓ core/ ($(ls -1 "$SOURCE_DIR/core/"*.js 2>/dev/null | wc -l | tr -d ' ') files)"
fi

# Action modules
if [ -d "$SOURCE_DIR/actions" ]; then
    cp "$SOURCE_DIR/actions/"*.js "$TARGET_DIR/actions/" 2>/dev/null || true
    echo "   ✓ actions/ ($(ls -1 "$SOURCE_DIR/actions/"*.js 2>/dev/null | wc -l | tr -d ' ') files)"
fi

# UI modules
if [ -d "$SOURCE_DIR/ui" ]; then
    cp "$SOURCE_DIR/ui/"*.js "$TARGET_DIR/ui/" 2>/dev/null || true
    echo "   ✓ ui/ ($(ls -1 "$SOURCE_DIR/ui/"*.js 2>/dev/null | wc -l | tr -d ' ') files)"
fi

# Entry point (deprecated, but copy for reference)
if [ -f "$SOURCE_DIR/monitor.js" ]; then
    cp "$SOURCE_DIR/monitor.js" "$TARGET_DIR/" 2>/dev/null || true
    echo "   ✓ monitor.js (entry point - deprecated)"
fi

echo ""
echo "✅ Monitor modules copied successfully!"
echo ""
echo "Target: $TARGET_DIR"
echo ""
echo "Structure:"
find "$TARGET_DIR" -name "*.js" -type f | sort | sed 's|^|  - |'
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
