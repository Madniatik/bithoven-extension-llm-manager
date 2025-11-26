<?php

namespace Bithoven\LLMManager\Database\Seeders\Data;

/**
 * LLM Manager Extension Permissions Data
 * 
 * Estructura de cada permiso:
 * [
 *     'name' => 'extensions:llm:scope:action',
 *     'alias' => 'Nombre Amigable',
 *     'description' => 'Descripción detallada del permiso'
 * ]
 */
class LLMPermissions
{
    /**
     * Get all LLM Manager extension permissions with alias and descriptions
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            // ===========================================
            // BASE (2 permisos)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:base:view',
                'alias' => 'Ver LLM Manager',
                'description' => 'Permite acceder al dashboard de LLM Manager y ver configuraciones generales. Incluye visualización de modelos, proveedores y estadísticas básicas.'
            ],
            [
                'name' => 'extensions:llm-manager:base:create',
                'alias' => 'Usar LLM Manager',
                'description' => 'Permite crear y ejecutar conversaciones con modelos LLM. Incluye envío de prompts, uso de tools y generación de respuestas.'
            ],

            // ===========================================
            // MODELS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:models:manage',
                'alias' => 'Gestionar Modelos LLM',
                'description' => 'Permite configurar y gestionar modelos de lenguaje. Incluye activación, desactivación, configuración de parámetros y selección de proveedores.'
            ],

            // ===========================================
            // PROVIDERS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:providers:manage',
                'alias' => 'Gestionar Proveedores',
                'description' => 'Permite configurar proveedores de LLM (OpenAI, Anthropic, Ollama, etc). Incluye gestión de API keys, endpoints y configuración de conexiones.'
            ],

            // ===========================================
            // CONNECTIONS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:connections:test',
                'alias' => 'Probar Conexiones',
                'description' => 'Permite ejecutar pruebas de conexión con proveedores LLM. Incluye validación de API keys, latencia y disponibilidad de modelos.'
            ],

            // ===========================================
            // CONVERSATIONS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:conversations:view',
                'alias' => 'Ver Conversaciones',
                'description' => 'Permite ver historial de conversaciones con LLMs. Incluye acceso a prompts enviados, respuestas generadas y métricas de uso.'
            ],

            // ===========================================
            // PROMPTS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:prompts:manage',
                'alias' => 'Gestionar Prompts',
                'description' => 'Permite crear, editar y gestionar prompts reutilizables. Incluye templates, variables dinámicas y versionamiento de prompts.'
            ],

            // ===========================================
            // TOOLS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:tools:manage',
                'alias' => 'Gestionar Tools (Function Calling)',
                'description' => 'Permite configurar tools y function calling para LLMs. Incluye definición de funciones, parámetros y conexión con MCP servers.'
            ],

            // ===========================================
            // WORKFLOWS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:workflows:manage',
                'alias' => 'Gestionar Workflows',
                'description' => 'Permite crear y gestionar workflows multi-agente. Incluye orquestación de múltiples LLMs, encadenamiento de prompts y automatizaciones.'
            ],

            // ===========================================
            // KNOWLEDGE (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:knowledge:manage',
                'alias' => 'Gestionar Base de Conocimiento',
                'description' => 'Permite gestionar bases de conocimiento para RAG (Retrieval Augmented Generation). Incluye indexación de documentos y embeddings.'
            ],

            // ===========================================
            // METRICS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:metrics:view',
                'alias' => 'Ver Métricas',
                'description' => 'Permite ver métricas detalladas de uso de LLMs. Incluye tokens consumidos, costos, latencia y rendimiento de modelos.'
            ],

            // ===========================================
            // STATS (1 permiso)
            // ===========================================
            [
                'name' => 'extensions:llm-manager:stats:view',
                'alias' => 'Ver Estadísticas',
                'description' => 'Permite ver estadísticas generales del sistema LLM. Incluye reportes de uso, gráficas de tendencias y análisis comparativo.'
            ],
        ];
    }

    /**
     * Get permissions grouped by scope
     * 
     * @return array
     */
    public static function byScope(): array
    {
        $all = self::all();
        $grouped = [];

        foreach ($all as $permission) {
            // Format: extensions:llm-manager:scope:action
            $parts = explode(':', $permission['name']);
            $scope = $parts[2] ?? 'other';
            
            if (!isset($grouped[$scope])) {
                $grouped[$scope] = [];
            }
            
            $grouped[$scope][] = $permission;
        }

        return $grouped;
    }

    /**
     * Get permission names only
     * 
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::all(), 'name');
    }
}
