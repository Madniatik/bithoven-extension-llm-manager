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

        // Assign permissions to roles based on access level
        $this->command->newLine();
        $this->command->info('Assigning permissions to roles...');
        
        try {
            // Super Admin: Full access (all 12 permissions)
            $superAdmin = Role::where('name', 'super-admin')->first();
            if ($superAdmin) {
                $superAdmin->givePermissionTo(LLMPermissions::names());
                $this->command->info('✅ super-admin: 12 permissions assigned');
            } else {
                $this->command->warn('⚠️  super-admin role not found');
            }
            
            // Master Developer: Full access (all 12 permissions)
            $masterDev = Role::where('name', 'master-developer')->first();
            if ($masterDev) {
                $masterDev->givePermissionTo(LLMPermissions::names());
                $this->command->info('✅ master-developer: 12 permissions assigned');
            }
            
            // Administrator: Basic management (5 permissions)
            $admin = Role::where('name', 'administrator')->first();
            if ($admin) {
                $admin->givePermissionTo([
                    'extensions:llm-manager:base:view',
                    'extensions:llm-manager:base:create',
                    'extensions:llm-manager:conversations:view',
                    'extensions:llm-manager:prompts:manage',
                    'extensions:llm-manager:stats:view',
                ]);
                $this->command->info('✅ administrator: 5 permissions assigned');
            }
            
            // Developer: Technical access without critical management (8 permissions)
            $developer = Role::where('name', 'developer')->first();
            if ($developer) {
                $developer->givePermissionTo([
                    'extensions:llm-manager:base:view',
                    'extensions:llm-manager:conversations:view',
                    'extensions:llm-manager:prompts:manage',
                    'extensions:llm-manager:tools:manage',
                    'extensions:llm-manager:workflows:manage',
                    'extensions:llm-manager:knowledge:manage',
                    'extensions:llm-manager:metrics:view',
                    'extensions:llm-manager:stats:view',
                ]);
                $this->command->info('✅ developer: 8 permissions assigned');
            }
            
            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
        } catch (\Exception $e) {
            $this->command->error('Failed to assign permissions to roles');
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
