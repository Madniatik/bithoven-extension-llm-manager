<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class LLMUserWorkspacePreferencesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Crea preferencias por defecto para todos los usuarios existentes
     * usando la configuración por defecto del ChatWorkspaceConfigValidator.
     *
     * @return void
     */
    public function run(): void
    {
        $defaultConfig = $this->getDefaultConfig();

        // Obtener todos los usuarios existentes
        $users = User::all();

        foreach ($users as $user) {
            // Insertar solo si no existe preferencia previa
            DB::table('llm_manager_user_workspace_preferences')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'config' => json_encode($defaultConfig),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info("Preferencias creadas para {$users->count()} usuarios.");
    }

    /**
     * Obtiene la configuración por defecto del workspace.
     *
     * Esta estructura replica ChatWorkspaceConfigValidator::getDefaults()
     *
     * @return array
     */
    private function getDefaultConfig(): array
    {
        return [
            'features' => [
                'monitor' => [
                    'enabled' => true,
                    'default_open' => false,
                    'tabs' => [
                        'console' => true,
                        'request_inspector' => true,
                        'activity_log' => true,
                    ],
                ],
                'settings_panel' => true,
                'persistence' => true,
                'toolbar' => true,
            ],
            'ui' => [
                'layout' => [
                    'chat' => 'bubble',
                    'monitor' => 'split-horizontal',
                ],
                'buttons' => [
                    'new_chat' => true,
                    'clear' => true,
                    'settings' => true,
                    'download' => true,
                    'monitor_toggle' => true,
                ],
                'mode' => 'full',
            ],
            'performance' => [
                'lazy_load_tabs' => true,
                'minify_assets' => false,
                'cache_preferences' => true,
            ],
            'advanced' => [
                'multi_instance' => false,
                'custom_css_class' => '',
                'debug_mode' => false,
            ],
        ];
    }
}
