# Sistema de Notificaciones en Tiempo Real

## Descripción
Se ha implementado un sistema completo de notificaciones en tiempo real para recordatorios de notas que funciona en todas las páginas del sistema.

## Características Implementadas

### 1. Verificación Automática
- **Frecuencia**: Cada 30 segundos
- **Alcance**: Verifica notas con recordatorios pendientes
- **Anticipación**: Muestra notificaciones 5 minutos antes del recordatorio

### 2. Notificaciones Visuales
- **Posición**: Esquina superior derecha
- **Diseño**: Alertas Bootstrap con iconos Font Awesome
- **Información mostrada**:
  - Asunto de la nota
  - Primeros 100 caracteres del mensaje
  - Fecha y hora del recordatorio
  - Botones de acción (Ver y Marcar como vista)

### 3. Funcionalidades Interactivas
- **Ver Nota**: Redirige a la página de notas resaltando la nota específica
- **Marcar como Vista**: Cambia el estado de la nota a 'completada'
- **Auto-ocultado**: Las notificaciones se ocultan automáticamente después de 10 segundos
- **Sonido**: Reproducción de sonido de notificación usando Web Audio API

### 4. Resaltado de Notas
- Cuando se accede desde una notificación, la nota se resalta con:
  - Fondo amarillo claro
  - Borde amarillo
  - Scroll automático para centrar la nota
  - Desvanecimiento del resaltado después de 5 segundos

## Archivos Implementados

### Backend
1. **`includes/notificaciones.php`**
   - Maneja las peticiones AJAX
   - Funciones para obtener notificaciones pendientes
   - Función para marcar notificaciones como vistas
   - Rutas dinámicas para compatibilidad con diferentes directorios

### Frontend
2. **`assets/js/notificaciones.js`**
   - Clase `SistemaNotificaciones` para manejo completo
   - Verificación periódica cada 30 segundos
   - Creación dinámica de notificaciones
   - Manejo de rutas relativas desde cualquier página
   - Reproducción de sonidos de notificación

### Integración
3. **`includes/footer.php`** (modificado)
   - Inclusión automática del JavaScript en todas las páginas
   - Actualización de Bootstrap a versión 5.3.3
   - Soporte para scripts adicionales por página

4. **`notas/lista_notas.php`** (modificado)
   - Atributo `data-nota-id` en filas de tabla
   - JavaScript para resaltado de notas específicas
   - Soporte para parámetro `highlight` en URL

## Cómo Funciona

### Flujo de Notificaciones
1. **Verificación**: Cada 30 segundos se consulta la base de datos
2. **Filtrado**: Se obtienen notas con recordatorios próximos (≤ 5 minutos)
3. **Visualización**: Se muestran notificaciones no vistas anteriormente
4. **Interacción**: Usuario puede ver la nota o marcarla como vista
5. **Actualización**: Estado de la nota se actualiza en la base de datos

### Rutas Dinámicas
El sistema detecta automáticamente desde qué directorio se está ejecutando:
- **Raíz del sistema**: `includes/notificaciones.php`
- **Subdirectorios**: `../includes/notificaciones.php`

### Base de Datos
Utiliza la tabla `notas` existente con los campos:
- `fecha_recordatorio`: Para determinar cuándo mostrar la notificación
- `estado`: Para controlar si la notificación ya fue vista
- `user_id`: Para mostrar solo las notas del usuario actual

## Configuración

### Personalización de Tiempos
- **Frecuencia de verificación**: Modificar `30000` en `notificaciones.js` (línea 12)
- **Tiempo de anticipación**: Modificar `5 MINUTE` en `notificaciones.php` (línea 13)
- **Auto-ocultado**: Modificar `10000` en `notificaciones.js` (línea 85)
- **Duración del resaltado**: Modificar `5000` en `lista_notas.php` (línea 285)

### Estilos CSS
Las notificaciones usan clases Bootstrap y estilos inline:
- `alert alert-warning alert-dismissible fade show`
- Sombra: `box-shadow: 0 4px 8px rgba(0,0,0,0.1)`
- Borde izquierdo: `border-left: 4px solid #ffc107`

## Seguridad
- Verificación de sesión activa
- Escape de HTML para prevenir XSS
- Validación de parámetros en peticiones AJAX
- Filtrado por usuario para mostrar solo notas propias

## Compatibilidad
- **Navegadores**: Modernos con soporte para Web Audio API
- **Responsive**: Adaptable a diferentes tamaños de pantalla
- **Accesibilidad**: Iconos descriptivos y títulos en botones

## Uso
1. **Crear nota con recordatorio**: Usar el formulario en la página de notas
2. **Esperar notificación**: El sistema verificará automáticamente
3. **Interactuar**: Hacer clic en "Ver" o "Marcar como vista"
4. **Seguimiento**: Las notas marcadas como vistas no volverán a notificar

El sistema está completamente integrado y funcionando en tiempo real en todas las páginas del sistema de ventas.