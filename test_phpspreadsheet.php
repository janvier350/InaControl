<?php
require_once 'class/AsistenciaController.php';

try {
    $controller = new AsistenciaController();
    echo "✅ AsistenciaController cargado correctamente\n";
    
    // Probar carga de PhpSpreadsheet
    $controller->cargarPhpSpreadsheet();
    echo "✅ PhpSpreadsheet cargado correctamente\n";
    
    echo "🎉 ¡Todo listo! Puedes subir archivos Excel.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>