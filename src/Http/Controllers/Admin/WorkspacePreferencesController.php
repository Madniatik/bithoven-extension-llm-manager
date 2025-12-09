<?php

namespace Bithoven\LLMManager\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Bithoven\LLMManager\Models\LLMUserWorkspacePreference;
use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;

class WorkspacePreferencesController extends Controller
{
    protected ChatWorkspaceConfigValidator $validator;

    public function __construct(ChatWorkspaceConfigValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get current user's workspace preferences.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $preference = LLMUserWorkspacePreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['config' => $this->validator->getDefaults()]
        );

        return response()->json([
            'success' => true,
            'config' => $preference->config,
        ]);
    }

    /**
     * Save workspace preferences for current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        try {
            // Validar datos entrantes
            $validated = $request->validate([
                'config' => 'required|array',
            ]);

            // Validar configuración con ChatWorkspaceConfigValidator
            $validatedConfig = $this->validator->validate($validated['config']);

            // Detectar si se necesita reload ANTES de guardar
            $needsReload = $this->detectReloadNeeded($request, $validatedConfig);

            // Actualizar o crear preferencia
            $preference = LLMUserWorkspacePreference::updateOrCreate(
                ['user_id' => $request->user()->id],
                ['config' => $validatedConfig]
            );

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully',
                'needs_reload' => $needsReload,
                'config' => $preference->config,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset preferences to defaults for current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $defaults = $this->validator->getDefaults();

            $preference = LLMUserWorkspacePreference::updateOrCreate(
                ['user_id' => $request->user()->id],
                ['config' => $defaults]
            );

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults',
                'needs_reload' => true, // Siempre reload en reset
                'config' => $preference->config,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resetting settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect if page reload is needed based on changed settings.
     *
     * Campos que requieren reload:
     * - features.monitor.enabled
     * - features.monitor.tabs.*
     * - ui.layout.chat
     * - ui.layout.monitor
     * - ui.mode
     *
     * @param Request $request
     * @param array $newConfig
     * @return bool
     */
    protected function detectReloadNeeded(Request $request, array $newConfig): bool
    {
        // Obtener configuración actual
        $currentPreference = LLMUserWorkspacePreference::where('user_id', $request->user()->id)->first();
        
        if (!$currentPreference) {
            return false; // Primera vez, no hay cambios previos
        }

        $currentConfig = $currentPreference->config;

        // Campos que requieren reload
        $reloadFields = [
            'features.monitor.enabled',
            'features.monitor.tabs.console',
            'features.monitor.tabs.request_inspector',
            'features.monitor.tabs.activity_log',
            'ui.layout.chat',
            'ui.layout.monitor',
            'ui.mode',
        ];

        foreach ($reloadFields as $field) {
            $currentValue = data_get($currentConfig, $field);
            $newValue = data_get($newConfig, $field);

            if ($currentValue !== $newValue) {
                return true;
            }
        }

        return false;
    }
}
