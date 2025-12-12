<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * LLM Manager Uninstall Seeder
 * 
 * Cleans up all LLM Manager permissions and role assignments during extension uninstallation.
 * Implements Extension Permissions Protocol v2.0 cleanup strategy.
 * 
 * @version 0.1.0
 */
class LLMUninstallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧹 Cleaning up LLM Manager permissions...');

        try {
            // Step 1: Delete role assignments
            $deletedAssignments = DB::table('role_has_permissions')
                ->whereIn('permission_id', function($query) {
                    $query->select('id')
                        ->from('permissions')
                        ->where('name', 'like', 'extensions:llm-manager:%');
                })
                ->delete();

            $this->command->comment("   Deleted {$deletedAssignments} role assignments");

            // Step 2: Delete permissions
            $deletedPermissions = Permission::where('name', 'like', 'extensions:llm-manager:%')->delete();

            $this->command->comment("   Deleted {$deletedPermissions} permissions");

            // Step 3: Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $this->command->newLine();
            $this->command->info("✅ LLM Manager permissions cleaned up successfully");
            $this->command->table(
                ['Item', 'Count'],
                [
                    ['Role Assignments Deleted', $deletedAssignments],
                    ['Permissions Deleted', $deletedPermissions],
                ]
            );

        } catch (\Exception $e) {
            $this->command->error('❌ Failed to cleanup LLM Manager permissions');
            $this->command->error($e->getMessage());
            
            // Don't throw - allow uninstall to continue even if cleanup fails
            logger()->error('LLM Manager uninstall seeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
