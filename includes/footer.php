</div> <!-- Cierre del container principal -->
    
    <!-- Bootstrap JS -->
    <script src="<?php echo isset($vendor_js_path) ? $vendor_js_path : '../assets/vendor/bootstrap.bundle.min.js'; ?>"></script>
    
    <!-- Sistema de Notificaciones -->
    <script src="<?php echo isset($js_path) ? $js_path : '../assets/js/notificaciones.js'; ?>"></script>
    
    <!-- Scripts adicionales específicos de página -->
    <?php if (isset($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
    
</body>
</html>