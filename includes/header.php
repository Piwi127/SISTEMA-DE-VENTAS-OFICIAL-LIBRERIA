<?php
/**
 * Header común para todas las páginas del sistema
 * Incluye meta tags, Bootstrap CSS, Font Awesome y estilos personalizados
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sistema de Ventas - Librería Belén</title>
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo isset($vendor_path) ? $vendor_path : '../assets/vendor/bootstrap.min.css'; ?>" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo isset($vendor_path) ? str_replace('bootstrap.min.css', 'fontawesome.min.css', $vendor_path) : '../assets/vendor/fontawesome.min.css'; ?>">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : '../assets/css/style.css'; ?>">
    
    <!-- Estilos adicionales específicos de página -->
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <?php 
    // Incluir navbar si no se especifica lo contrario
    if (!isset($hide_navbar) || !$hide_navbar) {
        $navbar_path = isset($navbar_path) ? $navbar_path : '../includes/navbar.php';
        if (file_exists($navbar_path)) {
            include_once $navbar_path;
        }
    }
    ?>