#!/bin/bash

# Script: migrate-all-versions.sh
# Purpose: Replace all v1.0.x version references with v0.x across entire extension
# Usage: ./scripts/migrate-all-versions.sh

set -e

EXTENSION_DIR="/Users/madniatik/CODE/LARAVEL/BITHOVEN/EXTENSIONS/bithoven-extension-llm-manager"
EXCLUDE_FILE="VERSIONING-MIGRATION-ANALYSIS.md"

cd "$EXTENSION_DIR"

echo "🔍 Starting comprehensive version migration..."
echo "📂 Directory: $EXTENSION_DIR"
echo "⚠️  Excluding: $EXCLUDE_FILE"
echo ""

# Function to replace version in all files except excluded
replace_version() {
    local old_version=$1
    local new_version=$2
    
    echo "   Replacing $old_version → $new_version"
    
    # Find all files (text-based) excluding the analysis document
    find . -type f \
        ! -path "./.git/*" \
        ! -path "./vendor/*" \
        ! -path "./node_modules/*" \
        ! -path "./storage/*" \
        ! -path "./public/*" \
        ! -path "./.idea/*" \
        ! -path "./.vscode/*" \
        ! -name "$EXCLUDE_FILE" \
        ! -name "*.jpg" \
        ! -name "*.png" \
        ! -name "*.gif" \
        ! -name "*.ico" \
        ! -name "*.pdf" \
        ! -name "*.zip" \
        ! -name "*.tar" \
        ! -name "*.gz" \
        -exec grep -l "$old_version" {} + 2>/dev/null | \
    while read -r file; do
        sed -i '' "s/$old_version/$new_version/g" "$file"
    done
}

# Replace versions in order (most specific first to avoid partial replacements)
echo "📝 Phase 1: Replacing v1.0.x references..."
replace_version "v1\.0\.8" "v0.4.0"
replace_version "v1\.0\.7" "v0.3.0"
replace_version "v1\.0\.6" "v0.2.2"
replace_version "v1\.0\.5" "v0.2.1"
replace_version "v1\.0\.4" "v0.2.0"
replace_version "v1\.0\.3" "v0.1.3"
replace_version "v1\.0\.2" "v0.1.2"
replace_version "v1\.0\.1" "v0.1.1"
replace_version "v1\.0\.0" "v0.1.0"

# Also replace version format without 'v' prefix in specific contexts
echo ""
echo "📝 Phase 2: Replacing 1.0.x references (without 'v' prefix)..."
replace_version "1\.0\.8" "0.4.0"
replace_version "1\.0\.7" "0.3.0"
replace_version "1\.0\.6" "0.2.2"
replace_version "1\.0\.5" "0.2.1"
replace_version "1\.0\.4" "0.2.0"
replace_version "1\.0\.3" "0.1.3"
replace_version "1\.0\.2" "0.1.2"
replace_version "1\.0\.1" "0.1.1"
replace_version "1\.0\.0" "0.1.0"

# Count affected files
AFFECTED_FILES=$(git diff --name-only | wc -l | xargs)

echo ""
echo "✅ Version migration complete!"
echo "📊 Files modified: $AFFECTED_FILES"
echo ""
echo "🔍 Verifying remaining v1.0.x references..."
REMAINING=$(grep -r "v1\.0\." . \
    --exclude-dir=.git \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    --exclude="$EXCLUDE_FILE" \
    2>/dev/null | wc -l | xargs)

if [ "$REMAINING" -eq 0 ]; then
    echo "✅ No remaining v1.0.x references found!"
else
    echo "⚠️  Found $REMAINING remaining v1.0.x references:"
    grep -rn "v1\.0\." . \
        --exclude-dir=.git \
        --exclude-dir=vendor \
        --exclude-dir=node_modules \
        --exclude="$EXCLUDE_FILE" \
        2>/dev/null | head -10
fi

echo ""
echo "💡 Next steps:"
echo "   1. Review changes: git diff"
echo "   2. Commit: git add . && git commit -m 'chore: complete version migration v1.0.x → v0.x'"
echo "   3. Push: git push origin main"
