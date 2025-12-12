#!/bin/bash

# ==============================================================================
# Script: rename-version-files.sh
# Description: Rename files and directories containing v1.0.x version references
#              to their corresponding v0.x versions
# Author: Copilot AI Agent
# Date: 12 de diciembre de 2025
# Version: 1.0.0
# ==============================================================================

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Version mapping function
map_version() {
    local old="$1"
    case "$old" in
        *"v1.0.0"*) echo "${old//v1.0.0/v0.1.0}" ;;
        *"v1.0.4"*) echo "${old//v1.0.4/v0.2.0}" ;;
        *"v1.0.5"*) echo "${old//v1.0.5/v0.2.1}" ;;
        *"v1.0.6"*) echo "${old//v1.0.6/v0.2.2}" ;;
        *"v1.0.7"*) echo "${old//v1.0.7/v0.3.0}" ;;
        *"v1.0.8"*) echo "${old//v1.0.8/v0.4.0}" ;;
        *"1.0.0"*) echo "${old//1.0.0/0.1.0}" ;;
        *"1.0.4"*) echo "${old//1.0.4/0.2.0}" ;;
        *"1.0.5"*) echo "${old//1.0.5/0.2.1}" ;;
        *"1.0.6"*) echo "${old//1.0.6/0.2.2}" ;;
        *"1.0.7"*) echo "${old//1.0.7/0.3.0}" ;;
        *"1.0.8"*) echo "${old//1.0.8/0.4.0}" ;;
        *) echo "$old" ;;
    esac
}

# Counter for renamed items
RENAMED_COUNT=0
SKIPPED_COUNT=0

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🔄 RENAMING FILES AND DIRECTORIES WITH VERSION REFERENCES${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Function to rename a file or directory
rename_item() {
    local old_path="$1"
    local new_path="$2"
    
    # Skip if already renamed or doesn't exist
    if [[ ! -e "$old_path" ]]; then
        return
    fi
    
    # Check if target already exists
    if [[ -e "$new_path" ]]; then
        echo -e "${YELLOW}⚠️  SKIP:${NC} Target already exists: $new_path"
        ((SKIPPED_COUNT++))
        return
    fi
    
    # Perform rename
    if mv "$old_path" "$new_path" 2>/dev/null; then
        echo -e "${GREEN}✓${NC} Renamed: ${BLUE}$old_path${NC} → ${GREEN}$new_path${NC}"
        ((RENAMED_COUNT++))
    else
        echo -e "${RED}✗${NC} Failed to rename: $old_path"
    fi
}

# Function to get new name for a path
get_new_name() {
    local old_name="$1"
    map_version "$old_name"
}

echo -e "${YELLOW}📋 Scanning for files and directories...${NC}"
echo ""

# Create arrays to store items to rename (files first, then directories)
declare -a FILES_TO_RENAME
declare -a DIRS_TO_RENAME

# Find all files first
while IFS= read -r item; do
    # Skip excluded paths
    if [[ "$item" == *"/.git/"* ]] || \
       [[ "$item" == *"/vendor/"* ]] || \
       [[ "$item" == *"/node_modules/"* ]] || \
       [[ "$item" == *"/VERSIONING-MIGRATION-ANALYSIS.md"* ]]; then
        continue
    fi
    
    # Get basename
    basename_item=$(basename "$item")
    
    # Check if item name contains any version reference
    if [[ "$basename_item" =~ (v)?1\.0\.[0-8] ]]; then
        FILES_TO_RENAME+=("$item")
    fi
done < <(find . -type f \( -name "*1.0.0*" -o -name "*1.0.4*" -o -name "*1.0.5*" -o -name "*1.0.6*" -o -name "*1.0.7*" -o -name "*1.0.8*" -o -name "*v1.0.0*" -o -name "*v1.0.4*" -o -name "*v1.0.5*" -o -name "*v1.0.6*" -o -name "*v1.0.7*" -o -name "*v1.0.8*" \))

# Then find directories (depth-first)
while IFS= read -r item; do
    # Skip excluded paths
    if [[ "$item" == *"/.git/"* ]] || \
       [[ "$item" == *"/vendor/"* ]] || \
       [[ "$item" == *"/node_modules/"* ]]; then
        continue
    fi
    
    # Get basename
    basename_item=$(basename "$item")
    
    # Check if item name contains any version reference
    if [[ "$basename_item" =~ (v)?1\.0\.[0-8] ]]; then
        DIRS_TO_RENAME+=("$item")
    fi
done < <(find . -depth -type d \( -name "*1.0.0*" -o -name "*1.0.4*" -o -name "*1.0.5*" -o -name "*1.0.6*" -o -name "*1.0.7*" -o -name "*1.0.8*" -o -name "*v1.0.0*" -o -name "*v1.0.4*" -o -name "*v1.0.5*" -o -name "*v1.0.6*" -o -name "*v1.0.7*" -o -name "*v1.0.8*" \))

# Combine arrays
declare -a ITEMS_TO_RENAME=("${FILES_TO_RENAME[@]}" "${DIRS_TO_RENAME[@]}")

# Display found items
echo -e "${BLUE}📊 Found ${#ITEMS_TO_RENAME[@]} items to rename${NC}"
echo ""

if [[ ${#ITEMS_TO_RENAME[@]} -eq 0 ]]; then
    echo -e "${GREEN}✓ No files or directories need renaming${NC}"
    exit 0
fi

# Process items (already in depth-first order)
echo -e "${YELLOW}🔄 Processing renames...${NC}"
echo ""

for item in "${ITEMS_TO_RENAME[@]}"; do
    # Get directory and basename
    dir_path=$(dirname "$item")
    base_name=$(basename "$item")
    
    # Get new name
    new_base_name=$(get_new_name "$base_name")
    
    # Skip if no change
    if [[ "$base_name" == "$new_base_name" ]]; then
        continue
    fi
    
    # Construct new full path
    new_path="$dir_path/$new_base_name"
    
    # Rename
    rename_item "$item" "$new_path"
done

# Summary
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ RENAME OPERATION COMPLETE${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "📊 ${GREEN}Successfully renamed:${NC} $RENAMED_COUNT items"
echo -e "⚠️  ${YELLOW}Skipped (already exists):${NC} $SKIPPED_COUNT items"
echo ""

# Verify remaining files with old versions
echo -e "${YELLOW}🔍 Verifying remaining files with old version patterns...${NC}"
REMAINING=$(find . \( -type f -o -type d \) \( -name "*1.0.0*" -o -name "*1.0.4*" -o -name "*1.0.5*" -o -name "*1.0.6*" -o -name "*1.0.7*" -o -name "*1.0.8*" -o -name "*v1.0.0*" -o -name "*v1.0.4*" -o -name "*v1.0.5*" -o -name "*v1.0.6*" -o -name "*v1.0.7*" -o -name "*v1.0.8*" \) | grep -v ".git" | grep -v "vendor" | grep -v "node_modules" | grep -v "VERSIONING-MIGRATION-ANALYSIS.md" | wc -l | xargs)

if [[ "$REMAINING" -eq 0 ]]; then
    echo -e "${GREEN}✓ Perfect! No remaining files with old version patterns${NC}"
else
    echo -e "${YELLOW}⚠️  Found $REMAINING items still containing old version patterns:${NC}"
    find . \( -type f -o -type d \) \( -name "*1.0.0*" -o -name "*1.0.4*" -o -name "*1.0.5*" -o -name "*1.0.6*" -o -name "*1.0.7*" -o -name "*1.0.8*" -o -name "*v1.0.0*" -o -name "*v1.0.4*" -o -name "*v1.0.5*" -o -name "*v1.0.6*" -o -name "*v1.0.7*" -o -name "*v1.0.8*" \) | grep -v ".git" | grep -v "vendor" | grep -v "node_modules" | grep -v "VERSIONING-MIGRATION-ANALYSIS.md" | head -20
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✨ Done! You can now commit these changes.${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
