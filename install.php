<?php
/**
 * Instalador Automático del Sistema de Ventas - Librería Belén
 * Este archivo ayuda a configurar automáticamente el sistema
 */

// Configuración de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = '';
$error = '';

// Función para verificar requisitos
function checkRequirements() {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'MBString Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'Session Support' => function_exists('session_start'),
        'Config Directory Writable' => is_writable(__DIR__ . '/config'),
    ];
    
    return $requirements;
}

// Función para crear la base de datos
function createDatabase($host, $user, $pass, $dbname) {
    try {
        // Conectar sin especificar base de datos
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear base de datos
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Conectar a la nueva base de datos
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Error de conexión: ' . $e->getMessage());
    }
}

// Función para crear archivo de configuración de base de datos (optimizada)
function createDatabaseConfig($host, $user, $pass, $dbname) {
    $config = "<?php\n";
    $config .= "// Configuración de la base de datos\n";
    $config .= "define('DB_HOST', '$host');\n";
    $config .= "define('DB_USER', '$user');\n";
    $config .= "define('DB_PASS', '$pass');\n";
    $config .= "define('DB_NAME', '$dbname');\n\n";
    $config .= "// Crear conexión\n";
    $config .= "try {\n";
    $config .= "    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8\", DB_USER, DB_PASS);\n";
    $config .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
    $config .= "    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n";
    $config .= "} catch(PDOException \$e) {\n";
    $config .= "    die(\"Error de conexión: \" . \$e->getMessage());\n";
    $config .= "}\n\n";
    $config .= "// Función para obtener la conexión\n";
    $config .= "function getConnection() {\n";
    $config .= "    global \$pdo;\n";
    $config .= "    return \$pdo;\n";
    $config .= "}\n";
    $config .= "?>";
    
    file_put_contents(__DIR__ . '/config/database.php', $config);
}

// Función para ejecutar el script SQL
function executeSQLFile($pdo, $filename) {
    $sql = file_get_contents($filename);
    if ($sql === false) {
        throw new Exception('No se pudo leer el archivo SQL');
    }
    
    // Dividir por punto y coma, pero ignorar los que están dentro de comillas
    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        
        if (!$inString && ($char === '"' || $char === "'")) {
            $inString = true;
            $stringChar = $char;
        } elseif ($inString && $char === $stringChar) {
            $inString = false;
        } elseif (!$inString && $char === ';') {
            $statement = trim($current);
            if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                $statements[] = $statement;
            }
            $current = '';
            continue;
        }
        
        $current .= $char;
    }
    
    // Agregar la última declaración si no está vacía
    $statement = trim($current);
    if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
        $statements[] = $statement;
    }
    
    // Ejecutar cada declaración
    foreach ($statements as $statement) {
        if (trim($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignorar errores de DROP TABLE y CREATE TABLE IF EXISTS
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), "doesn't exist") === false) {
                    throw $e;
                }
            }
        }
    }
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Verificar conexión a base de datos
        $host = $_POST['db_host'] ?? 'localhost';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        $dbname = $_POST['db_name'] ?? 'sistema_ventas_libreria';
        
        try {
            $pdo = createDatabase($host, $user, $pass, $dbname);
            
            // Crear configuración de base de datos usando función optimizada
            createDatabaseConfig($host, $user, $pass, $dbname);
            
            $step = 3;
            $message = 'Conexión exitosa. Configuración guardada.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($step === 3) {
        // Instalar base de datos
        try {
            require_once __DIR__ . '/config/database.php';
            $pdo = getConnection();
            
            // Ejecutar script SQL
            $sqlFile = __DIR__ . '/database/schema.sql';
            if (file_exists($sqlFile)) {
                executeSQLFile($pdo, $sqlFile);
                $step = 4;
                $message = 'Base de datos instalada correctamente.';
            } else {
                throw new Exception('Archivo schema.sql no encontrado.');
            }
        } catch (Exception $e) {
            $error = 'Error al instalar la base de datos: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Sistema de Ventas Librería Belén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .installer-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin: 2rem 0;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .requirement-check {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="installer-container p-4">
                    <div class="text-center mb-4">
                        <h2><i class="fas fa-book text-primary"></i> Librería Belén</h2>
                        <p class="text-muted">Instalador del Sistema de Ventas</p>
                    </div>
                    
                    <!-- Indicador de pasos -->
                    <div class="step-indicator">
                        <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                            1
                        </div>
                        <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                            2
                        </div>
                        <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                            3
                        </div>
                        <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">
                            4
                        </div>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($step === 1): ?>
                        <!-- Paso 1: Verificar requisitos -->
                        <h4>Paso 1: Verificación de Requisitos</h4>
                        <p class="text-muted">Verificando que el servidor cumple con los requisitos mínimos...</p>
                        
                        <?php
                        $requirements = checkRequirements();
                        $allPassed = true;
                        foreach ($requirements as $name => $passed) {
                            if (!$passed) $allPassed = false;
                            echo '<div class="requirement-check">';
                            echo '<span>' . $name . '</span>';
                            echo '<span class="badge bg-' . ($passed ? 'success' : 'danger') . '">';
                            echo '<i class="fas fa-' . ($passed ? 'check' : 'times') . '"></i> ';
                            echo ($passed ? 'OK' : 'FALTA');
                            echo '</span>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="mt-4">
                            <?php if ($allPassed): ?>
                                <a href="?step=2" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right"></i> Continuar
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <strong>Atención:</strong> Algunos requisitos no se cumplen. Por favor, instala las extensiones faltantes antes de continuar.
                                </div>
                                <button onclick="location.reload()" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-refresh"></i> Verificar Nuevamente
                                </button>
                            <?php endif; ?>
                        </div>
                        
                    <?php elseif ($step === 2): ?>
                        <!-- Paso 2: Configuración de base de datos -->
                        <h4>Paso 2: Configuración de Base de Datos</h4>
                        <p class="text-muted">Configura la conexión a tu base de datos MySQL.</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="db_host" class="form-label">Servidor de Base de Datos</label>
                                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                <small class="form-text text-muted">Generalmente 'localhost' para XAMPP</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="db_user" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                                <small class="form-text text-muted">Usuario por defecto en XAMPP: 'root'</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="db_pass" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="db_pass" name="db_pass" value="">
                                <small class="form-text text-muted">Dejar vacío para XAMPP por defecto</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="db_name" class="form-label">Nombre de la Base de Datos</label>
                                <input type="text" class="form-control" id="db_name" name="db_name" value="sistema_ventas_libreria" required>
                                <small class="form-text text-muted">Se creará automáticamente si no existe</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-database"></i> Probar Conexión
                            </button>
                        </form>
                        
                    <?php elseif ($step === 3): ?>
                        <!-- Paso 3: Instalar base de datos -->
                        <h4>Paso 3: Instalación de Base de Datos</h4>
                        <p class="text-muted">Crear las tablas y datos iniciales del sistema.</p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Se crearán todas las tablas necesarias y se insertarán datos de ejemplo.
                        </div>
                        
                        <form method="POST">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-cogs"></i> Instalar Base de Datos
                            </button>
                        </form>
                        
                    <?php elseif ($step === 4): ?>
                        <!-- Paso 4: Instalación completada -->
                        <h4>¡Instalación Completada!</h4>
                        <p class="text-muted">El sistema ha sido instalado correctamente.</p>
                        
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Sistema Listo</h5>
                            <p class="mb-2">Puedes acceder al sistema con las siguientes credenciales:</p>
                            <hr>
                            <strong>Administrador:</strong><br>
                            Email: admin@libreria.com<br>
                            Contraseña: admin123<br><br>
                            <strong>Vendedor:</strong><br>
                            Email: vendedor@libreria.com<br>
                            Contraseña: admin123
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Por seguridad, elimina o renombra este archivo de instalación (install.php) después de completar la configuración.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Acceder al Sistema
                            </a>
                            <a href="README.md" class="btn btn-outline-info" target="_blank">
                                <i class="fas fa-book"></i> Ver Documentación
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>