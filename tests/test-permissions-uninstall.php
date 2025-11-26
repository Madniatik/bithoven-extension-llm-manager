<?php

/**
 * Test script para verificar desinstalación de permisos
 * Ejecutar desde CPANEL: php ../EXTENSIONS/bithoven-extension-llm-manager/tests/test-permissions-uninstall.php
 */

require __DIR__ . '/../../../CPANEL/vendor/autoload.php';

// Crear app Laravel
$app = require_once __DIR__ . '/../../../CPANEL/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

echo "🧪 TEST: Desinstalación de Permisos LLM Manager\n";
echo "==============================================\n\n";

// 1. Verificar permisos antes de eliminar
echo "1️⃣ Permisos antes de eliminar:\n";
$before = Permission::where('name', 'like', 'extensions:llm-manager:%')->count();
echo "   Total: {$before}\n\n";

// 2. Eliminar asignaciones de roles
echo "2️⃣ Eliminando asignaciones de roles...\n";
$roleAssignments = DB::table('role_has_permissions')
    ->whereIn('permission_id', function($query) {
        $query->select('id')
            ->from('permissions')
            ->where('name', 'like', 'extensions:llm-manager:%');
    })
    ->delete();
echo "   Asignaciones eliminadas: {$roleAssignments}\n\n";

// 3. Eliminar permisos
echo "3️⃣ Eliminando permisos...\n";
$deleted = Permission::where('name', 'like', 'extensions:llm-manager:%')->delete();
echo "   Permisos eliminados: {$deleted}\n\n";

// 4. Verificar que no quedan residuos
echo "4️⃣ Verificando residuos...\n";
$after = Permission::where('name', 'like', 'extensions:llm-manager:%')->count();
echo "   Permisos residuales: {$after}\n";

if ($after === 0) {
    echo "   ✅ ÉXITO: No quedan permisos residuales\n";
} else {
    echo "   ❌ ERROR: Quedan {$after} permisos sin eliminar\n";
    
    $residual = Permission::where('name', 'like', 'extensions:llm-manager:%')->get();
    foreach ($residual as $perm) {
        echo "      - {$perm->name}\n";
    }
}

echo "\n✅ TEST COMPLETADO\n";
