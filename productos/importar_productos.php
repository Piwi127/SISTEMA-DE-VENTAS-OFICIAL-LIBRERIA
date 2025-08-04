<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Common\Type;

// Configuración de la base de datos
$db = new Database();
$conn = $db->getConnection();

// Ruta al archivo Excel
$filePath = __DIR__ . '/../public/BASE ACTUALIZADA.xlsx';

// Verificar si el archivo existe
if (!file_exists($filePath)) {
    die('El archivo Excel no se encontró en: ' . $filePath);
}

// Crear un lector para archivos XLSX
$reader = ReaderFactory::createFromType(Type::XLSX);
$reader->open($filePath);

echo "Iniciando importación de productos...\n";

$firstRow = true;
$importedCount = 0;

foreach ($reader->getSheets() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        $cells = $row->toArray();

        // Saltar la primera fila (encabezados)
        if ($firstRow) {
            $firstRow = false;
            continue;
        }

        // Asumiendo el orden de las columnas en el Excel:
        // [0] Nombre del Producto, [1] Descripción, [2] Categoría, [3] Precio, [4] Stock, [5] Stock Mínimo
        $nombre = $cells[0] ?? null;
        $descripcion = $cells[1] ?? null;
        $categoriaNombre = $cells[2] ?? null;
        $precio = $cells[3] ?? null;
        $stock = $cells[4] ?? null;
        $stockMinimo = $cells[5] ?? null;

        // Validar datos básicos
        if (empty($nombre) || empty($precio)) {
            echo "Saltando fila con datos incompletos: " . implode(', ', $cells) . "\n";
            continue;
        }

        // Generar un código único para el producto
        // Puedes usar un prefijo y un hash o un contador, por ejemplo:
        $codigo = 'PROD-' . uniqid(); // Genera un código único basado en el tiempo

        // Obtener o crear la categoría
        $categoriaId = null;
        if ($categoriaNombre) {
            $stmt = $conn->prepare("SELECT id FROM categorias WHERE nombre = ?");
            $stmt->bind_param("s", $categoriaNombre);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $categoriaId = $result->fetch_assoc()['id'];
            } else {
                // Si la categoría no existe, crearla
                $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->bind_param("s", $categoriaNombre);
                $stmt->execute();
                $categoriaId = $conn->insert_id;
                echo "Categoría '{$categoriaNombre}' creada con ID: {$categoriaId}\n";
            }
        }

        // Insertar el producto
        $stmt = $conn->prepare("INSERT INTO productos (codigo, nombre, descripcion, categoria_id, precio, stock, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiddi", $codigo, $nombre, $descripcion, $categoriaId, $precio, $stock, $stockMinimo);

        try {
            $stmt->execute();
            $importedCount++;
            echo "Producto '{$nombre}' importado con código '{$codigo}'\n";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Error de duplicado (código único)
                echo "Error: El código de producto '{$codigo}' ya existe para '{$nombre}'. Saltando.\n";
            } else {
                echo "Error al insertar producto '{$nombre}': " . $e->getMessage() . "\n";
            }
        }
    }
}

$reader->close();
$conn->close();

echo "Importación completada. Total de productos importados: {$importedCount}\n";

?>