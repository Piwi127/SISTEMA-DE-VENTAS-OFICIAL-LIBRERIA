<?php
// Script para configurar la base de datos manualmente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Conectar a MySQL
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Configurando Base de Datos...</h2>";
    
    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sistema_ventas_libreria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de datos creada<br>";
    
    // Conectar a la base de datos específica
    $pdo = new PDO("mysql:host=localhost;dbname=sistema_ventas_libreria;charset=utf8", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tablas básicas
    $sql = "
    CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) UNIQUE NOT NULL,
        nombre VARCHAR(200) NOT NULL,
        descripcion TEXT,
        categoria_id INT,
        precio DECIMAL(10,2) NOT NULL,
        stock INT DEFAULT 0,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100),
        email VARCHAR(150),
        telefono VARCHAR(20),
        direccion TEXT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'vendedor') DEFAULT 'vendedor',
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        usuario_id INT NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS detalle_ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venta_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL
    );
    ";
    
    $pdo->exec($sql);
    echo "✅ Tablas creadas<br>";
    
    // Insertar datos básicos
    $pdo->exec("INSERT IGNORE INTO categorias (nombre, descripcion) VALUES 
        ('Libros de Texto', 'Libros educativos y de texto escolar'),
        ('Literatura', 'Novelas, cuentos y literatura en general'),
        ('Papelería', 'Artículos de papelería y oficina')");
    
    $pdo->exec("INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES 
        ('Administrador', 'admin@libreria.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
        ('Vendedor Demo', 'vendedor@libreria.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor')");
    
    $pdo->exec("INSERT IGNORE INTO productos (codigo, nombre, descripcion, categoria_id, precio, stock) VALUES 
        ('LIB001', 'Cien Años de Soledad', 'Novela de Gabriel García Márquez', 2, 89.90, 15),
        ('LIB002', 'Don Quijote de la Mancha', 'Clásico de Miguel de Cervantes', 2, 119.90, 8),
        ('PAP001', 'Cuaderno Universitario', 'Cuaderno de 100 hojas rayado', 3, 12.50, 50),
        ('PAP002', 'Bolígrafo Azul', 'Bolígrafo de tinta azul', 3, 4.50, 100)");
    
    $pdo->exec("INSERT IGNORE INTO clientes (nombre, apellido, email, telefono) VALUES 
        ('Juan', 'Pérez', 'juan.perez@email.com', '555-0101'),
        ('María', 'González', 'maria.gonzalez@email.com', '555-0102'),
        ('Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '555-0103')");
    
    echo "✅ Datos de ejemplo insertados<br>";
    
    // Crear archivo de configuración
    $config = "<?php\n";
    $config .= "define('DB_HOST', 'localhost');\n";
    $config .= "define('DB_USER', 'root');\n";
    $config .= "define('DB_PASS', '');\n";
    $config .= "define('DB_NAME', 'sistema_ventas_libreria');\n\n";
    $config .= "try {\n";
    $config .= "    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8\", DB_USER, DB_PASS);\n";
    $config .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
    $config .= "    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n";
    $config .= "} catch(PDOException \$e) {\n";
    $config .= "    die(\"Error de conexión: \" . \$e->getMessage());\n";
    $config .= "}\n\n";
    $config .= "function getConnection() {\n";
    $config .= "    global \$pdo;\n";
    $config .= "    return \$pdo;\n";
    $config .= "}\n";
    $config .= "?>";
    
    file_put_contents(__DIR__ . '/config/database.php', $config);
    echo "✅ Archivo de configuración creado<br>";
    
    echo "<br><h3>🎉 ¡Configuración Completada!</h3>";
    echo "<p><strong>Credenciales:</strong></p>";
    echo "<p>Admin: admin@libreria.com / admin123</p>";
    echo "<p>Vendedor: vendedor@libreria.com / admin123</p>";
    echo "<br><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Acceder al Sistema</a>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de que XAMPP esté ejecutándose y MySQL esté activo.</p>";
}
?>