#!/bin/bash

#######################################################################
# CONDITIONAL RESOURCE LOADING - PERFORMANCE BENCHMARK
#
# Mide el tamaño de bundle (scripts + styles) cargado según config
# 
# Uso:
#   ./scripts/benchmark-conditional-loading.sh
#
# Escenarios:
#   1. ALL ENABLED (baseline): Monitor + todos los tabs + settings
#   2. MONITOR ONLY: Monitor enabled, 1 tab (console), sin settings
#   3. NO MONITOR: Monitor disabled, solo chat
#   4. MINIMAL: Solo chat, sin monitor, sin settings
#
# Output: Tabla comparativa con tamaño en KB y % de reducción
#######################################################################

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VIEWS_DIR="$SCRIPT_DIR/../resources/views/components/chat"
SCRIPTS_DIR="$VIEWS_DIR/partials/scripts"
STYLES_DIR="$VIEWS_DIR/partials/styles"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   CONDITIONAL RESOURCE LOADING - PERFORMANCE BENCHMARK${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo ""

#######################################################################
# Función para calcular tamaño total de archivos
#######################################################################
calculate_size() {
    local files=("$@")
    local total=0
    
    for file in "${files[@]}"; do
        if [ -f "$file" ]; then
            size=$(wc -c < "$file" 2>/dev/null || echo 0)
            total=$((total + size))
        fi
    done
    
    echo $total
}

#######################################################################
# Definir archivos por categoría
#######################################################################

# Core (SIEMPRE se cargan)
CORE_SCRIPTS=(
    "$SCRIPTS_DIR/clipboard-utils.blade.php"
    "$SCRIPTS_DIR/message-renderer.blade.php"
    "$SCRIPTS_DIR/event-handlers.blade.php"
    "$SCRIPTS_DIR/chat-workspace.blade.php"
)

CORE_STYLES=(
    "$STYLES_DIR/dependencies.blade.php"
    "$STYLES_DIR/markdown.blade.php"
    "$STYLES_DIR/buttons.blade.php"
    "$STYLES_DIR/responsive.blade.php"
)

# Settings Panel (conditional)
SETTINGS_SCRIPTS=(
    "$SCRIPTS_DIR/chat-settings.blade.php"
    "$SCRIPTS_DIR/settings-manager.blade.php"
)

SETTINGS_STYLES=(
    "$STYLES_DIR/chat-settings.blade.php"
)

# Monitor (conditional)
MONITOR_SCRIPTS=(
    "$SCRIPTS_DIR/monitor-api.blade.php"
    "$SCRIPTS_DIR/split-resizer.blade.php"
)

MONITOR_STYLES=(
    "$STYLES_DIR/split-horizontal.blade.php"
    "$STYLES_DIR/monitor-console.blade.php"
)

# Monitor Tabs (conditional por tab)
MONITOR_TAB_SCRIPTS=(
    "$SCRIPTS_DIR/request-inspector.blade.php"
)

#######################################################################
# ESCENARIO 1: ALL ENABLED (Baseline)
#######################################################################
echo -e "${YELLOW}📊 ESCENARIO 1: ALL ENABLED (Baseline)${NC}"
echo "   Config: monitor=true, all_tabs=true, settings=true"
echo ""

BASELINE_SIZE=0

# Core
CORE_SCRIPTS_SIZE=$(calculate_size "${CORE_SCRIPTS[@]}")
CORE_STYLES_SIZE=$(calculate_size "${CORE_STYLES[@]}")
BASELINE_SIZE=$((BASELINE_SIZE + CORE_SCRIPTS_SIZE + CORE_STYLES_SIZE))

# Settings
SETTINGS_SCRIPTS_SIZE=$(calculate_size "${SETTINGS_SCRIPTS[@]}")
SETTINGS_STYLES_SIZE=$(calculate_size "${SETTINGS_STYLES[@]}")
BASELINE_SIZE=$((BASELINE_SIZE + SETTINGS_SCRIPTS_SIZE + SETTINGS_STYLES_SIZE))

# Monitor
MONITOR_SCRIPTS_SIZE=$(calculate_size "${MONITOR_SCRIPTS[@]}")
MONITOR_STYLES_SIZE=$(calculate_size "${MONITOR_STYLES[@]}")
BASELINE_SIZE=$((BASELINE_SIZE + MONITOR_SCRIPTS_SIZE + MONITOR_STYLES_SIZE))

# Monitor Tabs
MONITOR_TAB_SCRIPTS_SIZE=$(calculate_size "${MONITOR_TAB_SCRIPTS[@]}")
BASELINE_SIZE=$((BASELINE_SIZE + MONITOR_TAB_SCRIPTS_SIZE))

BASELINE_KB=$((BASELINE_SIZE / 1024))
echo -e "   ${GREEN}Total Bundle Size: ${BASELINE_KB} KB${NC}"
echo ""

#######################################################################
# ESCENARIO 2: MONITOR ONLY (1 tab, sin settings)
#######################################################################
echo -e "${YELLOW}📊 ESCENARIO 2: MONITOR ONLY${NC}"
echo "   Config: monitor=true, console_only=true, settings=false"
echo ""

SCENARIO2_SIZE=$((CORE_SCRIPTS_SIZE + CORE_STYLES_SIZE + MONITOR_SCRIPTS_SIZE + MONITOR_STYLES_SIZE))
SCENARIO2_KB=$((SCENARIO2_SIZE / 1024))
REDUCTION2=$((100 - (SCENARIO2_SIZE * 100 / BASELINE_SIZE)))

echo -e "   Total Bundle Size: ${SCENARIO2_KB} KB"
echo -e "   ${GREEN}Reduction: ${REDUCTION2}%${NC}"
echo ""

#######################################################################
# ESCENARIO 3: NO MONITOR (solo chat + settings)
#######################################################################
echo -e "${YELLOW}📊 ESCENARIO 3: NO MONITOR${NC}"
echo "   Config: monitor=false, settings=true"
echo ""

SCENARIO3_SIZE=$((CORE_SCRIPTS_SIZE + CORE_STYLES_SIZE + SETTINGS_SCRIPTS_SIZE + SETTINGS_STYLES_SIZE))
SCENARIO3_KB=$((SCENARIO3_SIZE / 1024))
REDUCTION3=$((100 - (SCENARIO3_SIZE * 100 / BASELINE_SIZE)))

echo -e "   Total Bundle Size: ${SCENARIO3_KB} KB"
echo -e "   ${GREEN}Reduction: ${REDUCTION3}%${NC}"
echo ""

#######################################################################
# ESCENARIO 4: MINIMAL (solo chat)
#######################################################################
echo -e "${YELLOW}📊 ESCENARIO 4: MINIMAL${NC}"
echo "   Config: monitor=false, settings=false"
echo ""

SCENARIO4_SIZE=$((CORE_SCRIPTS_SIZE + CORE_STYLES_SIZE))
SCENARIO4_KB=$((SCENARIO4_SIZE / 1024))
REDUCTION4=$((100 - (SCENARIO4_SIZE * 100 / BASELINE_SIZE)))

echo -e "   Total Bundle Size: ${SCENARIO4_KB} KB"
echo -e "   ${GREEN}Reduction: ${REDUCTION4}%${NC}"
echo ""

#######################################################################
# TABLA COMPARATIVA
#######################################################################
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   COMPARATIVE TABLE${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo ""

printf "%-25s %-15s %-15s\n" "Scenario" "Bundle Size" "Reduction"
printf "%-25s %-15s %-15s\n" "────────────────────────" "───────────────" "───────────────"
printf "%-25s %-15s %-15s\n" "1. ALL ENABLED (baseline)" "${BASELINE_KB} KB" "0%"
printf "%-25s %-15s %-15s\n" "2. MONITOR ONLY" "${SCENARIO2_KB} KB" "${REDUCTION2}%"
printf "%-25s %-15s %-15s\n" "3. NO MONITOR" "${SCENARIO3_KB} KB" "${REDUCTION3}%"
printf "%-25s %-15s %-15s\n" "4. MINIMAL" "${SCENARIO4_KB} KB" "${REDUCTION4}%"
echo ""

#######################################################################
# BREAKDOWN BY CATEGORY
#######################################################################
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   BREAKDOWN BY CATEGORY${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo ""

CORE_TOTAL_KB=$(( (CORE_SCRIPTS_SIZE + CORE_STYLES_SIZE) / 1024 ))
SETTINGS_TOTAL_KB=$(( (SETTINGS_SCRIPTS_SIZE + SETTINGS_STYLES_SIZE) / 1024 ))
MONITOR_TOTAL_KB=$(( (MONITOR_SCRIPTS_SIZE + MONITOR_STYLES_SIZE) / 1024 ))
MONITOR_TAB_TOTAL_KB=$(( MONITOR_TAB_SCRIPTS_SIZE / 1024 ))

printf "%-30s %-15s\n" "Category" "Size"
printf "%-30s %-15s\n" "──────────────────────────────" "───────────────"
printf "%-30s %-15s\n" "Core (always loaded)" "${CORE_TOTAL_KB} KB"
printf "%-30s %-15s\n" "Settings Panel" "${SETTINGS_TOTAL_KB} KB"
printf "%-30s %-15s\n" "Monitor (base)" "${MONITOR_TOTAL_KB} KB"
printf "%-30s %-15s\n" "Monitor Tabs (request_insp)" "${MONITOR_TAB_TOTAL_KB} KB"
echo ""

echo -e "${GREEN}✅ Benchmark completado${NC}"
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
