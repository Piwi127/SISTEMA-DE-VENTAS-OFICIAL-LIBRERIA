-- Base de datos para Sistema de Ventas - Librería Belén
-- Ejecutar este script en phpMyAdmin o MySQL Workbench

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sistema_ventas_libreria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_ventas_libreria;

-- Tabla de categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombre),
    INDEX idx_categoria (categoria_id)
);

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    telefono VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    codigo_postal VARCHAR(10),
    fecha_nacimiento DATE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_email (email),
    INDEX idx_telefono (telefono)
);

-- Tabla de usuarios del sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor') DEFAULT 'vendedor',
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Tabla de ventas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    usuario_id INT NOT NULL,
    subtotal DECIMAL(10,2) DEFAULT 0,
    impuestos DECIMAL(10,2) DEFAULT 0,
    descuento DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('completada', 'pendiente', 'cancelada') DEFAULT 'completada',
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_fecha (fecha),
    INDEX idx_cliente (cliente_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
);

-- Tabla de detalle de ventas
CREATE TABLE detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_venta (venta_id),
    INDEX idx_producto (producto_id)
);

-- Tabla de movimientos de stock
CREATE TABLE movimientos_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    motivo VARCHAR(200),
    usuario_id INT,
    venta_id INT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    INDEX idx_producto (producto_id),
    INDEX idx_fecha (fecha),
    INDEX idx_tipo (tipo)
);

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion VARCHAR(255),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos iniciales

-- Categorías de ejemplo
INSERT INTO categorias (nombre, descripcion) VALUES
('Libros de Texto', 'Libros educativos y de texto escolar'),
('Literatura', 'Novelas, cuentos y literatura en general'),
('Infantil', 'Libros para niños y jóvenes'),
('Técnicos', 'Libros técnicos y especializados'),
('Papelería', 'Artículos de papelería y oficina'),
('Material Escolar', 'Útiles escolares y material educativo');

-- Usuario administrador por defecto
-- Contraseña: admin123 (hasheada con password_hash)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@libreria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Vendedor Demo', 'vendedor@libreria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor');

-- Productos de ejemplo
INSERT INTO productos (codigo, nombre, descripcion, categoria_id, precio, stock, stock_minimo) VALUES
('LIB001', 'Cien Años de Soledad', 'Novela de Gabriel García Márquez', 2, 25.99, 15, 3),
('LIB002', 'Don Quijote de la Mancha', 'Clásico de Miguel de Cervantes', 2, 32.50, 8, 2),
('LIB003', 'Matemáticas 1° Secundaria', 'Libro de texto para primer año', 1, 45.00, 25, 5),
('LIB004', 'El Principito', 'Libro infantil clásico', 3, 18.75, 20, 4),
('PAP001', 'Cuaderno Universitario', 'Cuaderno de 100 hojas rayado', 5, 3.50, 50, 10),
('PAP002', 'Bolígrafo Azul', 'Bolígrafo de tinta azul', 5, 1.25, 100, 20),
('ESC001', 'Regla 30cm', 'Regla de plástico transparente', 6, 2.75, 30, 8),
('LIB005', 'Programación en PHP', 'Manual técnico de programación', 4, 65.00, 5, 2);

-- Clientes de ejemplo
INSERT INTO clientes (nombre, apellido, email, telefono, direccion, ciudad) VALUES
('Juan', 'Pérez', 'juan.perez@email.com', '555-0101', 'Calle Principal 123', 'Ciudad Principal'),
('María', 'González', 'maria.gonzalez@email.com', '555-0102', 'Avenida Central 456', 'Ciudad Principal'),
('Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '555-0103', 'Calle Secundaria 789', 'Ciudad Principal'),
('Ana', 'Martínez', 'ana.martinez@email.com', '555-0104', 'Boulevard Norte 321', 'Ciudad Principal'),
('Luis', 'López', 'luis.lopez@email.com', '555-0105', 'Calle Sur 654', 'Ciudad Principal');

-- Configuración inicial del sistema
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('nombre_empresa', 'Librería Belén', 'Nombre de la empresa'),
('direccion_empresa', 'Calle Principal 100, Ciudad Principal', 'Dirección de la empresa'),
('telefono_empresa', '555-0100', 'Teléfono de la empresa'),
('email_empresa', 'info@libreriabelen.com', 'Email de contacto'),
('moneda', 'MXN', 'Moneda utilizada en el sistema'),
('iva_porcentaje', '16', 'Porcentaje de IVA aplicado'),
('stock_minimo_global', '5', 'Stock mínimo por defecto para productos nuevos');

-- Ventas de ejemplo
INSERT INTO ventas (cliente_id, usuario_id, total, fecha) VALUES
(1, 1, 71.49, '2024-01-15 10:30:00'),
(2, 2, 28.25, '2024-01-15 14:45:00'),
(3, 1, 156.75, '2024-01-16 09:15:00'),
(1, 2, 45.00, '2024-01-16 16:20:00'),
(4, 1, 89.50, '2024-01-17 11:10:00');

-- Detalle de ventas de ejemplo
INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
-- Venta 1
(1, 1, 2, 25.99, 51.98),
(1, 5, 5, 3.50, 17.50),
(1, 6, 1, 1.25, 1.25),
-- Venta 2
(2, 4, 1, 18.75, 18.75),
(2, 7, 3, 2.75, 8.25),
(2, 6, 1, 1.25, 1.25),
-- Venta 3
(3, 3, 3, 45.00, 135.00),
(3, 5, 6, 3.50, 21.00),
(3, 6, 1, 1.25, 1.25),
-- Venta 4
(4, 3, 1, 45.00, 45.00),
-- Venta 5
(5, 8, 1, 65.00, 65.00),
(5, 1, 1, 25.99, 25.99);

-- Crear triggers para movimientos de stock
DELIMITER //

-- Trigger para registrar movimientos de stock en ventas
CREATE TRIGGER after_detalle_venta_insert
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    DECLARE stock_anterior INT;
    
    -- Obtener stock anterior
    SELECT stock INTO stock_anterior FROM productos WHERE id = NEW.producto_id;
    
    -- Registrar movimiento de stock
    INSERT INTO movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, venta_id)
    VALUES (NEW.producto_id, 'salida', NEW.cantidad, stock_anterior, stock_anterior - NEW.cantidad, 'Venta', NEW.venta_id);
END//

-- Trigger para actualizar stock después de insertar movimiento
CREATE TRIGGER after_movimiento_stock_insert
AFTER INSERT ON movimientos_stock
FOR EACH ROW
BEGIN
    IF NEW.tipo = 'salida' THEN
        UPDATE productos SET stock = stock - NEW.cantidad WHERE id = NEW.producto_id;
    ELSEIF NEW.tipo = 'entrada' THEN
        UPDATE productos SET stock = stock + NEW.cantidad WHERE id = NEW.producto_id;
    ELSEIF NEW.tipo = 'ajuste' THEN
        UPDATE productos SET stock = NEW.stock_nuevo WHERE id = NEW.producto_id;
    END IF;
END//

DELIMITER ;

-- Crear vistas útiles

-- Vista de productos con información de categoría
CREATE VIEW vista_productos AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.descripcion,
    p.precio,
    p.stock,
    p.stock_minimo,
    p.activo,
    c.nombre as categoria_nombre,
    p.fecha_creacion,
    p.fecha_actualizacion
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id;

-- Vista de ventas con información completa
CREATE VIEW vista_ventas AS
SELECT 
    v.id,
    v.total,
    v.fecha,
    v.estado,
    v.metodo_pago,
    c.nombre as cliente_nombre,
    c.email as cliente_email,
    u.nombre as vendedor_nombre,
    COUNT(dv.id) as total_items
FROM ventas v
LEFT JOIN clientes c ON v.cliente_id = c.id
LEFT JOIN usuarios u ON v.usuario_id = u.id
LEFT JOIN detalle_ventas dv ON v.id = dv.venta_id
GROUP BY v.id;

-- Índices adicionales para optimización
CREATE INDEX idx_productos_stock_bajo ON productos(stock, stock_minimo);
CREATE INDEX idx_ventas_fecha_total ON ventas(fecha, total);
CREATE INDEX idx_clientes_activos ON clientes(activo, nombre);

-- Comentarios en las tablas
ALTER TABLE productos COMMENT = 'Tabla de productos del inventario';
ALTER TABLE clientes COMMENT = 'Tabla de clientes registrados';
ALTER TABLE ventas COMMENT = 'Tabla de ventas realizadas';
ALTER TABLE detalle_ventas COMMENT = 'Detalle de productos vendidos en cada venta';
ALTER TABLE movimientos_stock COMMENT = 'Historial de movimientos de inventario';
ALTER TABLE usuarios COMMENT = 'Usuarios del sistema de ventas';
ALTER TABLE categorias COMMENT = 'Categorías de productos';
ALTER TABLE configuracion COMMENT = 'Configuración general del sistema';

-- Mensaje de finalización
SELECT 'Base de datos creada exitosamente. Sistema listo para usar.' as mensaje;