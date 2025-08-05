# Plan de Implementación - Módulo de Venta Libre

## Descripción General
El módulo de venta libre permitirá registrar ventas de productos o servicios especiales que no están en el inventario regular, como cotizaciones especiales, trabajos manuales, investigaciones, etc.

## Estructura del Formulario

### Campos del Formulario
1. **Motivo de Venta** (textarea)
   - Descripción del motivo por el cual se realiza la venta
   - Campo obligatorio
   - Placeholder: "Ej: Trabajo de investigación, cotización especial, etc."

2. **Descripción** (textarea)
   - Descripción detallada del producto o servicio
   - Campo obligatorio
   - Placeholder: "Describa detalladamente el producto o servicio"

3. **Cantidad** (number)
   - Cantidad de productos/servicios
   - Campo obligatorio
   - Valor mínimo: 1
   - Valor por defecto: 1

4. **Total** (number)
   - Precio total de la venta
   - Campo obligatorio
   - Formato: decimal con 2 decimales
   - Validación: mayor a 0

### Botones de Acción
1. **Generar Venta**
   - Procesa y registra la venta en la base de datos
   - Valida todos los campos antes de procesar
   - Genera número de venta automático

2. **Imprimir Boleta**
   - Genera boleta en formato PDF para impresión
   - Solo disponible después de generar la venta

3. **Descargar Boleta**
   - Descarga la boleta en formato PDF
   - Solo disponible después de generar la venta

## Estructura de Base de Datos

### Tabla: ventas_libres
```sql
CREATE TABLE ventas_libres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_venta VARCHAR(20) UNIQUE NOT NULL,
    motivo_venta TEXT NOT NULL,
    descripcion TEXT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    estado ENUM('activa', 'anulada') DEFAULT 'activa',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

## Archivos a Crear/Modificar

### 1. Nuevos Archivos
- `ventas/venta_libre.php` - Formulario principal
- `ventas/procesar_venta_libre.php` - Procesamiento del formulario
- `ventas/generar_boleta_libre_pdf.php` - Generación de PDF
- `ventas/lista_ventas_libres.php` - Listado de ventas libres

### 2. Archivos a Modificar
- `includes/navbar.php` - Agregar enlace al módulo
- `index.php` - Agregar estadísticas de ventas libres
- `database/schema.sql` - Agregar tabla ventas_libres

## Funcionalidades Específicas

### Generación de Número de Venta
- Formato: VL-YYYYMMDD-XXXX
- VL = Venta Libre
- YYYY = Año
- MM = Mes
- DD = Día
- XXXX = Número secuencial del día

### Integración con Dashboard
- Agregar contador de ventas libres en el dashboard principal
- Mostrar total de ingresos por ventas libres
- Gráfico de ventas libres por período

### Validaciones
1. **Frontend (JavaScript)**
   - Validación de campos obligatorios
   - Validación de formato numérico
   - Validación de valores mínimos

2. **Backend (PHP)**
   - Sanitización de datos
   - Validación de tipos de datos
   - Verificación de permisos de usuario

### Permisos y Seguridad
- Solo usuarios autenticados pueden acceder
- Registro de auditoría para cada venta libre
- Validación de sesión en cada operación

## Diseño de Interfaz

### Layout
- Utilizar Bootstrap para responsive design
- Formulario en card con sombra
- Campos organizados en grid de 2 columnas
- Botones con iconos de FontAwesome

### Colores y Estilos
- Mantener consistencia con el diseño actual
- Botón "Generar Venta": btn-primary
- Botón "Imprimir Boleta": btn-success
- Botón "Descargar Boleta": btn-info

## Flujo de Trabajo

1. **Acceso al Módulo**
   - Usuario navega a "Ventas" > "Venta Libre"
   - Se muestra el formulario vacío

2. **Llenado del Formulario**
   - Usuario completa todos los campos
   - Validación en tiempo real

3. **Generación de Venta**
   - Click en "Generar Venta"
   - Validación y procesamiento
   - Registro en base de datos
   - Mensaje de confirmación

4. **Generación de Boleta**
   - Botones de imprimir/descargar se habilitan
   - Generación de PDF con formato estándar

## Reportes y Estadísticas

### Dashboard Principal
- Widget con total de ventas libres del día
- Widget con ingresos por ventas libres del mes
- Comparativa con período anterior

### Reportes Específicos
- Reporte de ventas libres por período
- Reporte de ventas libres por usuario
- Reporte de motivos más frecuentes

## Consideraciones Técnicas

### Performance
- Índices en campos de búsqueda frecuente
- Paginación en listados
- Cache para consultas frecuentes

### Backup y Recuperación
- Incluir tabla ventas_libres en respaldos
- Procedimientos de recuperación

### Escalabilidad
- Estructura preparada para múltiples sucursales
- Campos adicionales para futuras funcionalidades

## Cronograma de Implementación

### Fase 1: Base de Datos y Backend (2-3 horas)
- Crear tabla ventas_libres
- Implementar funciones de procesamiento
- Crear archivo de procesamiento

### Fase 2: Frontend (2-3 horas)
- Diseñar formulario principal
- Implementar validaciones JavaScript
- Integrar con backend

### Fase 3: Generación de PDF (1-2 horas)
- Adaptar generador de boletas existente
- Personalizar para ventas libres

### Fase 4: Integración y Testing (1-2 horas)
- Integrar con navbar y dashboard
- Pruebas de funcionalidad
- Ajustes finales

## Notas Adicionales

- Mantener consistencia con el sistema actual
- Reutilizar componentes existentes cuando sea posible
- Documentar todas las funciones nuevas
- Implementar logs para auditoría
- Considerar futuras mejoras como descuentos o impuestos