<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Common\Type;

// Configuración de la base de datos
$conn = getConnection();

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

foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        $cells = $row->toArray();

        // Saltar la primera fila (encabezados)
        if ($firstRow) {
            $firstRow = false;
            continue;
        }

        // Estructura real del Excel:
        // [0] Nombre del Producto, [1] Precio, [2] Stock, [3] ?, [4] Categoría
        $nombre = $cells[0] ?? null;
        $precio = $cells[1] ?? null;
        $stock = $cells[2] ?? null;
        $categoriaNombre = $cells[4] ?? null;
        $descripcion = null; // No hay descripción en el Excel
        $stockMinimo = 5; // Valor por defecto

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
            $stmt->execute([$categoriaNombre]);
             $result = $stmt->fetch(PDO::FETCH_ASSOC);
             if ($result) {
                 $categoriaId = $result['id'];
            } else {
                // Si la categoría no existe, crearla
                $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->execute([$categoriaNombre]);
                $categoriaId = $conn->lastInsertId();
                echo "Categoría '{$categoriaNombre}' creada con ID: {$categoriaId}\n";
            }
        }

        // Insertar el producto
        try {
            $stmt = $conn->prepare("INSERT INTO productos (codigo, nombre, descripcion, categoria_id, precio, stock, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$codigo, $nombre, $descripcion, $categoriaId, $precio, $stock, $stockMinimo]);
            $importedCount++;
            echo "Producto '{$nombre}' importado con código '{$codigo}'\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Error de duplicado (código único)
                echo "Error: El código de producto '{$codigo}' ya existe para '{$nombre}'. Saltando.\n";
            } else {
                echo "Error al insertar producto '{$nombre}': " . $e->getMessage() . "\n";
            }
        }
    }
}

$reader->close();
$conn = null;

echo "Importación completada. Total de productos importados: {$importedCount}\n";

?>