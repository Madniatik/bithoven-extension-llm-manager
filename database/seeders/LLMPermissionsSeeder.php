<?php

namespace Bithoven\LLMManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Bithoven\LLMManager\Database\Seeders\Data\LLMPermissions;

class LLMPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates all LLM Manager permissions with alias and descriptions
     * following Extension Permissions Protocol v2.0
     */
    public function run(): void
    {
        $this->command->info('Installing LLM Manager permissions...');

        $permissions = LLMPermissions::all();
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($permissions as $permissionData) {
            try {
                $permission = Permission::firstOrNew(['name' => $permissionData['name']]);
                
                if ($permission->exists) {
                    // Update existing permission
                    $permission->alias = $permissionData['alias'];
                    $permission->description = $permissionData['description'];
                    $permission->save();
                    $updatedCount++;
                    $this->command->comment("Updated: {$permissionData['name']}");
                } else {
                    // Create new permission
                    $permission->alias = $permissionData['alias'];
                    $permission->description = $permissionData['description'];
                    $permission->guard_name = 'web';
                    $permission->save();
                    $createdCount++;
                    $this->command->info("Created: {$permissionData['name']}");
                }
            } catch (\Exception $e) {
                $this->command->error("Failed to create/update permission: {$permissionData['name']}");
                $this->command->error($e->getMessage());
                $skippedCount++;
            }
        }

        // Assign permissions to Super Admin role
        try {
            $superAdmin = Role::where('name', 'Super Admin')->first();
            if ($superAdmin) {
                $superAdmin->givePermissionTo(LLMPermissions::names());
                $this->command->info('✅ Permissions assigned to Super Admin role');
            } else {
                $this->command->warn('⚠️  Super Admin role not found, permissions not assigned to any role');
            }
        } catch (\Exception $e) {
            $this->command->error('Failed to assign permissions to Super Admin');
            $this->command->error($e->getMessage());
        }

        $this->command->newLine();
        $this->command->info('LLM Manager Permissions Installation Summary:');
        $this->command->table(
            ['Status', 'Count'],
            [
                ['Created', $createdCount],
                ['Updated', $updatedCount],
                ['Skipped', $skippedCount],
                ['Total', count($permissions)],
            ]
        );
    }
}
