#!/bin/bash

# ============================================================================
# Script para subir bithoven-extension-llm-manager a GitHub
# ============================================================================

echo "════════════════════════════════════════════════════════════"
echo "   📦 PREPARANDO PUSH A GITHUB"
echo "════════════════════════════════════════════════════════════"
echo ""

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}PASO 1: Crear repositorio en GitHub${NC}"
echo ""
echo "Ve a: https://github.com/new"
echo ""
echo "Configuración:"
echo "  • Repository name: bithoven-extension-llm-manager"
echo "  • Description: Multi-provider LLM Manager for Laravel with RAG, Workflows & MCP"
echo "  • Visibility: Public (recomendado) o Private"
echo "  • ❌ NO marcar 'Add README'"
echo "  • ❌ NO marcar 'Add .gitignore'"
echo "  • ❌ NO marcar 'Add license'"
echo ""
echo "Click 'Create repository'"
echo ""
read -p "Presiona ENTER cuando hayas creado el repo en GitHub..."

echo ""
echo -e "${YELLOW}PASO 2: Obtener la URL del repositorio${NC}"
echo ""
echo "GitHub te mostrará comandos. Copia la URL que aparece después de:"
echo "  git remote add origin"
echo ""
echo "Ejemplo: https://github.com/Madniatik/bithoven-extension-llm-manager.git"
echo ""
read -p "Pega la URL del repo: " REPO_URL

if [ -z "$REPO_URL" ]; then
    echo -e "${YELLOW}No se proporcionó URL. Usando URL por defecto...${NC}"
    REPO_URL="https://github.com/Madniatik/bithoven-extension-llm-manager.git"
fi

echo ""
echo -e "${BLUE}Usando URL: $REPO_URL${NC}"
echo ""

echo -e "${YELLOW}PASO 3: Configurando remote y haciendo push...${NC}"
echo ""

# Agregar remote
git remote add origin "$REPO_URL"

# Verificar
echo "Remote configurado:"
git remote -v
echo ""

# Push
echo "Haciendo push..."
git push -u origin main

# Push tags
echo ""
echo "Subiendo tags..."
git push origin --tags

echo ""
echo "════════════════════════════════════════════════════════════"
echo -e "${GREEN}   ✅ PUSH COMPLETADO!${NC}"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "Repo URL: $REPO_URL"
echo "Branch: main"
echo "Tag: v1.0.0-pre-installation"
echo "Commits: 1"
echo "Files: 104"
echo ""
echo "Próximos pasos:"
echo "  1. Unit Tests"
echo "  2. Instalación en CPANEL"
echo "  3. Feature Tests"
echo "  4. Integration Tests"
echo ""
echo "════════════════════════════════════════════════════════════"
