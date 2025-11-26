<?php

/**
 * Test script para verificar instalación de permisos
 * Ejecutar desde CPANEL: php ../EXTENSIONS/bithoven-extension-llm-manager/tests/test-permissions-install.php
 */

require __DIR__ . '/../../../CPANEL/vendor/autoload.php';
require __DIR__ . '/../database/seeders/data/LLMPermissions.php';

// Crear app Laravel
$app = require_once __DIR__ . '/../../../CPANEL/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Bithoven\LLMManager\Database\Seeders\Data\LLMPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "🧪 TEST: Instalación de Permisos LLM Manager\n";
echo "==========================================\n\n";

// 1. Limpiar permisos existentes
echo "1️⃣ Limpiando permisos existentes...\n";
$deleted = Permission::where('name', 'like', 'extensions:llm-manager:%')->delete();
echo "   Eliminados: {$deleted}\n\n";

// 2. Obtener permisos de data class
$permissions = LLMPermissions::all();
echo "2️⃣ Permisos en LLMPermissions::all(): " . count($permissions) . "\n\n";

// 3. Crear permisos
echo "3️⃣ Creando permisos...\n";
$createdCount = 0;
$skippedCount = 0;

foreach ($permissions as $permissionData) {
    $permission = Permission::firstOrCreate(
        ['name' => $permissionData['name']],
        [
            'alias' => $permissionData['alias'],
            'description' => $permissionData['description'],
            'guard_name' => 'web'
        ]
    );

    if ($permission->wasRecentlyCreated) {
        $createdCount++;
        echo "   ✅ Creado: {$permissionData['name']}\n";
        echo "      Alias: {$permissionData['alias']}\n";
    } else {
        $skippedCount++;
        echo "   ⏭️  Ya existe: {$permissionData['name']}\n";
    }
}

echo "\n4️⃣ Resumen de creación:\n";
echo "   Creados: {$createdCount}\n";
echo "   Saltados: {$skippedCount}\n";
echo "   Total: " . count($permissions) . "\n\n";

// 4. Verificar en base de datos
$dbPermissions = Permission::where('name', 'like', 'extensions:llm-manager:%')->get();
echo "5️⃣ Permisos en DB: " . $dbPermissions->count() . "\n";

foreach ($dbPermissions as $perm) {
    $hasAlias = !empty($perm->alias) ? '✅' : '❌';
    $hasDesc = !empty($perm->description) ? '✅' : '❌';
    echo "   {$perm->name}\n";
    echo "      Alias {$hasAlias}: {$perm->alias}\n";
    echo "      Desc {$hasDesc}: " . substr($perm->description, 0, 60) . "...\n";
}

// 5. Asignar a Super Admin
echo "\n6️⃣ Asignando a Super Admin...\n";
$superAdmin = Role::where('name', 'Super Admin')->first();
if ($superAdmin) {
    $superAdmin->givePermissionTo(LLMPermissions::names());
    $assigned = $superAdmin->permissions()->where('name', 'like', 'extensions:llm-manager:%')->count();
    echo "   ✅ Asignados: {$assigned} permisos\n";
} else {
    echo "   ❌ Super Admin role not found\n";
}

echo "\n✅ TEST COMPLETADO\n";
