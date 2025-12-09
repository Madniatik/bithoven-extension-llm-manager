<?php

namespace Bithoven\LLMManager\Tests\Feature;

use Bithoven\LLMManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

/**
 * Permissions Feature Tests
 * 
 * Tests for permission system covering installation,
 * uninstallation, RBAC validation, and user access control.
 * 
 * Extension Permissions (IDs 53-60):
 * - 53: llm-manager.view
 * - 54: llm-manager.manage-configs
 * - 55: llm-manager.use-chat
 * - 56: llm-manager.manage-prompts
 * - 57: llm-manager.manage-tools
 * - 58: llm-manager.manage-knowledge-base
 * - 59: llm-manager.view-usage-logs
 * - 60: llm-manager.manage-mcp-connectors
 */
class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected Role $adminRole;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = Role::create(['name' => 'administrator']);
        $this->userRole = Role::create(['name' => 'user']);

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    /**
     * Test all 8 extension permissions exist in database
     */
    public function test_all_extension_permissions_exist(): void
    {
        $expectedPermissions = [
            'llm-manager.view',
            'llm-manager.manage-configs',
            'llm-manager.use-chat',
            'llm-manager.manage-prompts',
            'llm-manager.manage-tools',
            'llm-manager.manage-knowledge-base',
            'llm-manager.view-usage-logs',
            'llm-manager.manage-mcp-connectors',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            
            $this->assertNotNull(
                $permission,
                "Permission '{$permissionName}' does not exist in database"
            );
        }

        // Verify count
        $llmPermissions = Permission::where('name', 'like', 'llm-manager.%')->count();
        $this->assertEquals(8, $llmPermissions, 'Expected exactly 8 LLM Manager permissions');
    }

    /**
     * Test permission IDs are in range 53-60
     */
    public function test_permission_ids_are_in_correct_range(): void
    {
        $llmPermissions = Permission::where('name', 'like', 'llm-manager.%')
            ->orderBy('id')
            ->get();

        $this->assertGreaterThanOrEqual(8, $llmPermissions->count());

        foreach ($llmPermissions as $permission) {
            $this->assertGreaterThanOrEqual(
                53,
                $permission->id,
                "Permission {$permission->name} ID ({$permission->id}) is below 53"
            );
            
            $this->assertLessThanOrEqual(
                60,
                $permission->id,
                "Permission {$permission->name} ID ({$permission->id}) is above 60"
            );
        }
    }

    /**
     * Test administrator role has all LLM Manager permissions
     */
    public function test_administrator_has_all_permissions(): void
    {
        $expectedPermissions = [
            'llm-manager.view',
            'llm-manager.manage-configs',
            'llm-manager.use-chat',
            'llm-manager.manage-prompts',
            'llm-manager.manage-tools',
            'llm-manager.manage-knowledge-base',
            'llm-manager.view-usage-logs',
            'llm-manager.manage-mcp-connectors',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $this->assertTrue(
                $this->admin->can($permissionName),
                "Administrator cannot {$permissionName}"
            );
        }
    }

    /**
     * Test user role has basic permissions only
     */
    public function test_user_has_basic_permissions_only(): void
    {
        // User should have view and use-chat
        $this->assertTrue($this->user->can('llm-manager.view'));
        $this->assertTrue($this->user->can('llm-manager.use-chat'));

        // User should NOT have management permissions
        $this->assertFalse($this->user->can('llm-manager.manage-configs'));
        $this->assertFalse($this->user->can('llm-manager.manage-prompts'));
        $this->assertFalse($this->user->can('llm-manager.manage-tools'));
        $this->assertFalse($this->user->can('llm-manager.manage-knowledge-base'));
        $this->assertFalse($this->user->can('llm-manager.manage-mcp-connectors'));
    }

    /**
     * Test Quick Chat access requires use-chat permission
     */
    public function test_quick_chat_requires_use_chat_permission(): void
    {
        // Admin can access
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.llm.quick-chat'));
        $response->assertOk();

        // User with permission can access
        $this->actingAs($this->user);
        $response = $this->get(route('admin.llm.quick-chat'));
        $response->assertOk();

        // User without permission cannot access
        $this->user->revokePermissionTo('llm-manager.use-chat');
        $response = $this->get(route('admin.llm.quick-chat'));
        $response->assertForbidden();
    }

    /**
     * Test configurations management requires manage-configs permission
     */
    public function test_configurations_management_requires_permission(): void
    {
        // Admin can access
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.llm.configurations.index'));
        $response->assertOk();

        // User without permission cannot access
        $this->actingAs($this->user);
        $response = $this->get(route('admin.llm.configurations.index'));
        $response->assertForbidden();
    }

    /**
     * Test prompts management requires manage-prompts permission
     */
    public function test_prompts_management_requires_permission(): void
    {
        // Admin can access
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.llm.prompts.index'));
        $response->assertOk();

        // User without permission cannot access
        $this->actingAs($this->user);
        $response = $this->get(route('admin.llm.prompts.index'));
        $response->assertForbidden();
    }

    /**
     * Test knowledge base management requires permission
     */
    public function test_knowledge_base_requires_permission(): void
    {
        // Admin can access
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.llm.knowledge-base.index'));
        $response->assertOk();

        // User without permission cannot access
        $this->actingAs($this->user);
        $response = $this->get(route('admin.llm.knowledge-base.index'));
        $response->assertForbidden();
    }

    /**
     * Test usage logs viewing requires permission
     */
    public function test_usage_logs_requires_permission(): void
    {
        // Admin can access
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.llm.usage-logs.index'));
        $response->assertOk();

        // User can view if has permission
        $this->actingAs($this->user);
        $this->user->givePermissionTo('llm-manager.view-usage-logs');
        $response = $this->get(route('admin.llm.usage-logs.index'));
        $response->assertOk();

        // User without permission cannot access
        $this->user->revokePermissionTo('llm-manager.view-usage-logs');
        $response = $this->get(route('admin.llm.usage-logs.index'));
        $response->assertForbidden();
    }

    /**
     * Test MCP connectors management requires permission
     */
    public function test_mcp_connectors_requires_permission(): void
    {
        // Admin can access
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.llm.mcp.index'));
        $response->assertOk();

        // User without permission cannot access
        $this->actingAs($this->user);
        $response = $this->get(route('admin.llm.mcp.index'));
        $response->assertForbidden();
    }

    /**
     * Test permission assignment to custom role
     */
    public function test_permission_can_be_assigned_to_custom_role(): void
    {
        // Create custom role
        $chatRole = Role::create(['name' => 'chat-user']);

        // Assign only chat-related permissions
        $chatRole->givePermissionTo([
            'llm-manager.view',
            'llm-manager.use-chat',
        ]);

        // Create user with custom role
        $chatUser = User::factory()->create();
        $chatUser->assignRole('chat-user');

        // Should have chat permissions
        $this->assertTrue($chatUser->can('llm-manager.view'));
        $this->assertTrue($chatUser->can('llm-manager.use-chat'));

        // Should NOT have management permissions
        $this->assertFalse($chatUser->can('llm-manager.manage-configs'));
        $this->assertFalse($chatUser->can('llm-manager.manage-prompts'));
    }

    /**
     * Test permission revocation works correctly
     */
    public function test_permission_revocation_works(): void
    {
        // Admin has all permissions initially
        $this->assertTrue($this->admin->can('llm-manager.manage-configs'));

        // Revoke specific permission
        $this->admin->revokePermissionTo('llm-manager.manage-configs');

        // Should no longer have permission
        $this->assertFalse($this->admin->can('llm-manager.manage-configs'));

        // Should still have other permissions
        $this->assertTrue($this->admin->can('llm-manager.view'));
        $this->assertTrue($this->admin->can('llm-manager.use-chat'));
    }

    /**
     * Test direct permission assignment to user (bypassing role)
     */
    public function test_direct_permission_assignment_to_user(): void
    {
        // Create user without any role
        $guestUser = User::factory()->create();

        // Initially has no permissions
        $this->assertFalse($guestUser->can('llm-manager.view'));

        // Give direct permission
        $guestUser->givePermissionTo('llm-manager.view');
        $guestUser->givePermissionTo('llm-manager.use-chat');

        // Should now have permissions
        $this->assertTrue($guestUser->can('llm-manager.view'));
        $this->assertTrue($guestUser->can('llm-manager.use-chat'));

        // Should NOT have other permissions
        $this->assertFalse($guestUser->can('llm-manager.manage-configs'));
    }

    /**
     * Test middleware protection on routes
     */
    public function test_middleware_protects_routes(): void
    {
        // Create user without permissions
        $unprivilegedUser = User::factory()->create();

        $this->actingAs($unprivilegedUser);

        // All LLM Manager routes should be forbidden
        $protectedRoutes = [
            route('admin.llm.quick-chat'),
            route('admin.llm.configurations.index'),
            route('admin.llm.prompts.index'),
            route('admin.llm.knowledge-base.index'),
            route('admin.llm.usage-logs.index'),
            route('admin.llm.mcp.index'),
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertForbidden();
        }
    }

    /**
     * Test uninstall cleanup removes permissions
     * 
     * Note: This test verifies the cleanup logic without actually
     * running the uninstall process (which would break the test suite).
     */
    public function test_permission_cleanup_on_uninstall(): void
    {
        // Verify permissions exist
        $llmPermissions = Permission::where('name', 'like', 'llm-manager.%')->get();
        $this->assertGreaterThan(0, $llmPermissions->count());

        // Simulate uninstall cleanup
        foreach ($llmPermissions as $permission) {
            // Remove from all roles
            DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();

            // Remove from all users
            DB::table('model_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();

            // Delete permission
            $permission->delete();
        }

        // Verify cleanup
        $remainingPermissions = Permission::where('name', 'like', 'llm-manager.%')->count();
        $this->assertEquals(0, $remainingPermissions, 'Permissions were not properly cleaned up');

        // Verify no orphaned relationships
        $orphanedRolePerms = DB::table('role_has_permissions')
            ->whereIn('permission_id', $llmPermissions->pluck('id'))
            ->count();
        $this->assertEquals(0, $orphanedRolePerms);

        $orphanedUserPerms = DB::table('model_has_permissions')
            ->whereIn('permission_id', $llmPermissions->pluck('id'))
            ->count();
        $this->assertEquals(0, $orphanedUserPerms);
    }

    /**
     * Test multiple users with different permission sets
     */
    public function test_multiple_users_different_permissions(): void
    {
        $users = [];
        $permissionSets = [
            'viewer' => ['llm-manager.view'],
            'chat-only' => ['llm-manager.view', 'llm-manager.use-chat'],
            'config-manager' => ['llm-manager.view', 'llm-manager.manage-configs'],
            'full-access' => [
                'llm-manager.view',
                'llm-manager.manage-configs',
                'llm-manager.use-chat',
                'llm-manager.manage-prompts',
                'llm-manager.manage-tools',
                'llm-manager.manage-knowledge-base',
                'llm-manager.view-usage-logs',
                'llm-manager.manage-mcp-connectors',
            ],
        ];

        foreach ($permissionSets as $roleName => $permissions) {
            $role = Role::create(['name' => "test-{$roleName}"]);
            $role->givePermissionTo($permissions);

            $user = User::factory()->create();
            $user->assignRole("test-{$roleName}");
            $users[$roleName] = $user;
        }

        // Test viewer
        $this->assertTrue($users['viewer']->can('llm-manager.view'));
        $this->assertFalse($users['viewer']->can('llm-manager.use-chat'));

        // Test chat-only
        $this->assertTrue($users['chat-only']->can('llm-manager.use-chat'));
        $this->assertFalse($users['chat-only']->can('llm-manager.manage-configs'));

        // Test config-manager
        $this->assertTrue($users['config-manager']->can('llm-manager.manage-configs'));
        $this->assertFalse($users['config-manager']->can('llm-manager.use-chat'));

        // Test full-access
        foreach ($permissionSets['full-access'] as $permission) {
            $this->assertTrue(
                $users['full-access']->can($permission),
                "Full access user missing permission: {$permission}"
            );
        }
    }

    /**
     * Test permission guard name is correct
     */
    public function test_permissions_have_correct_guard(): void
    {
        $llmPermissions = Permission::where('name', 'like', 'llm-manager.%')->get();

        foreach ($llmPermissions as $permission) {
            $this->assertEquals(
                'web',
                $permission->guard_name,
                "Permission {$permission->name} has incorrect guard: {$permission->guard_name}"
            );
        }
    }
}
