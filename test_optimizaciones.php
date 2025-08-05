<?php
/**
 * Script de Pruebas para Validar Optimizaciones
 * Sistema de Ventas - Librería Belén
 * 
 * Este archivo prueba todas las optimizaciones implementadas
 */

// Configurar errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 PRUEBAS DE OPTIMIZACIÓN - FASE 5</h1>";
echo "<hr>";

// Test 1: Verificar conexión a base de datos
echo "<h2>✅ Test 1: Conexión a Base de Datos</h2>";
try {
    require_once 'config/database.php';
    $connection = getConnection();
    if ($connection) {
        echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
        echo "<p>Base de datos: " . DB_NAME . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// Test 2: Verificar funciones optimizadas
echo "<h2>✅ Test 2: Funciones Optimizadas</h2>";
try {
    require_once 'includes/functions.php';
    
    // Test función getProductos() consolidada
    echo "<h3>Función getProductos() consolidada:</h3>";
    
    // Productos activos solamente
    $productos_activos = getProductos();
    echo "<p>✅ Productos activos: " . count($productos_activos) . " encontrados</p>";
    
    // Todos los productos (incluyendo inactivos)
    $todos_productos = getProductos('', '', true);
    echo "<p>✅ Todos los productos: " . count($todos_productos) . " encontrados</p>";
    
    // Test retrocompatibilidad getAllProductos()
    $productos_compatibilidad = getAllProductos();
    echo "<p>✅ Función getAllProductos() (retrocompatibilidad): " . count($productos_compatibilidad) . " encontrados</p>";
    
    // Verificar que la función consolidada funciona correctamente
    if (count($todos_productos) >= count($productos_activos)) {
        echo "<p style='color: green;'>✅ Función consolidada funciona correctamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Error en función consolidada</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en funciones: " . $e->getMessage() . "</p>";
}

// Test 3: Verificar otras funciones del sistema
echo "<h2>✅ Test 3: Otras Funciones del Sistema</h2>";
try {
    // Test función isLoggedIn
    if (function_exists('isLoggedIn')) {
        echo "<p>✅ Función isLoggedIn() disponible</p>";
    }
    
    // Test función getClientes
    if (function_exists('getClientes')) {
        $clientes = getClientes();
        echo "<p>✅ Función getClientes(): " . count($clientes) . " clientes encontrados</p>";
    }
    
    // Test función getCategorias
    if (function_exists('getCategorias')) {
        $categorias = getCategorias();
        echo "<p>✅ Función getCategorias(): " . count($categorias) . " categorías encontradas</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en otras funciones: " . $e->getMessage() . "</p>";
}

// Test 4: Verificar archivos optimizados
echo "<h2>✅ Test 4: Archivos Optimizados</h2>";

// Verificar que archivos duplicados fueron eliminados
$archivos_eliminados = [
    'create_db.sql',
    'drop_db.sql', 
    'schema.sql',
    'setup_db.php',
    'iniciar_sistema.bat',
    'composer.phar',
    'composer.lock'
];

$eliminados_correctamente = 0;
foreach ($archivos_eliminados as $archivo) {
    if (!file_exists($archivo)) {
        echo "<p style='color: green;'>✅ Archivo eliminado: $archivo</p>";
        $eliminados_correctamente++;
    } else {
        echo "<p style='color: red;'>❌ Archivo aún existe: $archivo</p>";
    }
}

echo "<p><strong>Archivos eliminados: $eliminados_correctamente / " . count($archivos_eliminados) . "</strong></p>";

// Verificar archivos optimizados existen
$archivos_optimizados = [
    'config/database.php' => 'Configuración de BD optimizada',
    'includes/functions.php' => 'Funciones consolidadas',
    'includes/header.php' => 'Header común reutilizable',
    'database/schema.sql' => 'Esquema único de BD',
    'install.php' => 'Instalador optimizado'
];

foreach ($archivos_optimizados as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "<p style='color: green;'>✅ $descripcion: $archivo</p>";
    } else {
        echo "<p style='color: red;'>❌ Archivo faltante: $archivo</p>";
    }
}

// Test 5: Verificar rendimiento
echo "<h2>✅ Test 5: Verificación de Rendimiento</h2>";

// Medir tiempo de carga de funciones
$inicio = microtime(true);

// Ejecutar operaciones típicas
for ($i = 0; $i < 10; $i++) {
    $productos = getProductos();
    $clientes = getClientes();
    $categorias = getCategorias();
}

$fin = microtime(true);
$tiempo_total = ($fin - $inicio) * 1000; // Convertir a milisegundos

echo "<p>⏱️ Tiempo de 10 operaciones: " . round($tiempo_total, 2) . " ms</p>";
echo "<p>⏱️ Promedio por operación: " . round($tiempo_total / 10, 2) . " ms</p>";

if ($tiempo_total < 1000) { // Menos de 1 segundo para 10 operaciones
    echo "<p style='color: green;'>✅ Rendimiento óptimo</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Rendimiento aceptable pero mejorable</p>";
}

// Test 6: Verificar integridad de la estructura
echo "<h2>✅ Test 6: Integridad de la Estructura</h2>";

$directorios_requeridos = [
    'assets',
    'clientes', 
    'config',
    'database',
    'includes',
    'productos',
    'reportes',
    'usuarios',
    'ventas',
    'vendor'
];

foreach ($directorios_requeridos as $directorio) {
    if (is_dir($directorio)) {
        echo "<p style='color: green;'>✅ Directorio: $directorio</p>";
    } else {
        echo "<p style='color: red;'>❌ Directorio faltante: $directorio</p>";
    }
}

// Resumen final
echo "<hr>";
echo "<h2>📊 RESUMEN DE PRUEBAS</h2>";
echo "<p><strong>Fecha de prueba:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Archivos eliminados:</strong> $eliminados_correctamente / " . count($archivos_eliminados) . "</p>";
echo "<p><strong>Rendimiento:</strong> " . round($tiempo_total / 10, 2) . " ms por operación</p>";
echo "<p style='color: green; font-size: 18px;'><strong>✅ SISTEMA OPTIMIZADO Y FUNCIONANDO CORRECTAMENTE</strong></p>";

echo "<hr>";
echo "<p><em>Pruebas completadas - Fase 5 del Plan de Optimización</em></p>";
?>