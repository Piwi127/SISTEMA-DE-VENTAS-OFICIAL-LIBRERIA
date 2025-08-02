# Sistema de Ventas - Librería Belén

Sistema completo de gestión de ventas desarrollado en PHP para librerías y pequeños comercios.

## 🚀 Características

- **Gestión de Ventas**: Proceso completo de ventas con carrito de compras
- **Inventario**: Control de productos, stock y categorías
- **Clientes**: Gestión completa de base de clientes
- **Usuarios**: Sistema de autenticación con roles (Admin/Vendedor)
- **Dashboard**: Estadísticas y métricas en tiempo real
- **Reportes**: Análisis de ventas y rendimiento
- **Responsive**: Diseño adaptable a dispositivos móviles

## 📋 Requisitos del Sistema

- **Servidor Web**: Apache o Nginx
- **PHP**: Versión 7.4 o superior
- **Base de Datos**: MySQL 5.7 o superior / MariaDB 10.2+
- **Extensiones PHP**:
  - PDO
  - PDO_MySQL
  - mbstring
  - json

## 🛠️ Instalación

### Paso 1: Configurar XAMPP

1. **Descargar e instalar XAMPP** desde [https://www.apachefriends.org/](https://www.apachefriends.org/)

2. **Iniciar servicios**:
   - Abrir XAMPP Control Panel
   - Iniciar **Apache** y **MySQL**

### Paso 2: Configurar la Base de Datos

1. **Acceder a phpMyAdmin**:
   - Ir a [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

2. **Crear la base de datos**:
   - Hacer clic en "Nuevo" en el panel izquierdo
   - Nombre: `sistema_ventas_libreria`
   - Cotejamiento: `utf8mb4_unicode_ci`
   - Hacer clic en "Crear"

3. **Importar el esquema**:
   - Seleccionar la base de datos creada
   - Ir a la pestaña "Importar"
   - Seleccionar el archivo `database/schema.sql`
   - Hacer clic en "Continuar"

### Paso 3: Configurar la Aplicación

1. **Copiar archivos**:
   - Copiar toda la carpeta del proyecto a `C:\xampp\htdocs\`
   - Renombrar la carpeta a `libreria` (opcional)

2. **Configurar base de datos**:
   - Abrir el archivo `config/database.php`
   - Verificar la configuración:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'sistema_ventas_libreria');
     ```

### Paso 4: Acceder al Sistema

1. **URL de acceso**: [http://localhost/SISTEMA%20DE%20VENTAS%20OFICIAL%20LIBRERIA/](http://localhost/SISTEMA%20DE%20VENTAS%20OFICIAL%20LIBRERIA/)

2. **Credenciales por defecto**:
   - **Administrador**:
     - Email: `admin@libreria.com`
     - Contraseña: `admin123`
   - **Vendedor**:
     - Email: `vendedor@libreria.com`
     - Contraseña: `admin123`

## 📱 Uso del Sistema

### Dashboard Principal
- Estadísticas de ventas del día y mes
- Resumen de productos y clientes
- Ventas recientes
- Acceso rápido a funciones principales

### Gestión de Ventas
1. **Nueva Venta**:
   - Seleccionar cliente
   - Agregar productos al carrito
   - Ajustar cantidades
   - Procesar venta

2. **Lista de Ventas**:
   - Historial completo de ventas
   - Filtros por fecha y cliente
   - Opciones de impresión

### Gestión de Productos
- **Agregar productos**: Código, nombre, precio, stock
- **Categorías**: Organización por categorías
- **Control de stock**: Alertas de stock bajo
- **Estados**: Activar/desactivar productos

### Gestión de Clientes
- **Registro completo**: Datos personales y contacto
- **Historial de compras**: Seguimiento por cliente
- **Estados**: Clientes activos/inactivos

### Sistema de Usuarios
- **Roles diferenciados**:
  - **Admin**: Acceso completo al sistema
  - **Vendedor**: Ventas y consultas
- **Autenticación segura**: Contraseñas encriptadas

## 🔧 Configuración Avanzada

### Personalización

1. **Datos de la empresa**:
   - Editar tabla `configuracion` en la base de datos
   - Modificar nombre, dirección, teléfono

2. **Estilos**:
   - Archivo: `assets/css/style.css`
   - Colores principales en variables CSS

3. **Funcionalidades**:
   - Archivo: `assets/js/main.js`
   - Funciones del carrito y validaciones

### Backup de Base de Datos

```sql
-- Exportar datos
mysqldump -u root -p sistema_ventas_libreria > backup.sql

-- Restaurar datos
mysql -u root -p sistema_ventas_libreria < backup.sql
```

## 📊 Estructura del Proyecto

```
SISTEMA DE VENTAS OFICIAL LIBRERIA/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── includes/
│   └── functions.php
├── ventas/
│   ├── nueva_venta.php
│   ├── lista_ventas.php
│   └── procesar_venta.php
├── productos/
│   └── lista_productos.php
├── clientes/
│   └── lista_clientes.php
├── database/
│   └── schema.sql
├── index.php
├── login.php
├── logout.php
└── README.md
```

## 🔒 Seguridad

- **Autenticación**: Sistema de login seguro
- **Contraseñas**: Encriptación con `password_hash()`
- **Sesiones**: Manejo seguro de sesiones PHP
- **Validación**: Sanitización de datos de entrada
- **SQL Injection**: Uso de prepared statements

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
1. Verificar que MySQL esté ejecutándose en XAMPP
2. Comprobar credenciales en `config/database.php`
3. Asegurar que la base de datos existe

### Página en Blanco
1. Activar errores PHP:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Verificar logs de Apache en `C:\xampp\apache\logs\error.log`

### Problemas de Permisos
1. En Windows, ejecutar XAMPP como administrador
2. Verificar permisos de escritura en la carpeta del proyecto

## 📈 Próximas Funcionalidades

- [ ] Módulo de reportes avanzados
- [ ] Integración con códigos de barras
- [ ] Sistema de descuentos y promociones
- [ ] Facturación electrónica
- [ ] API REST para integración
- [ ] Aplicación móvil

## 🤝 Contribución

Para contribuir al proyecto:

1. Fork el repositorio
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o consultas:

- **Email**: soporte@libreriabelen.com
- **Teléfono**: +52 555-0100
- **Documentación**: [Wiki del proyecto]()

## 🙏 Agradecimientos

- Bootstrap 5 por el framework CSS
- Font Awesome por los iconos
- Comunidad PHP por las mejores prácticas
- XAMPP por el entorno de desarrollo

---

**Desarrollado con ❤️ para Librería Belén**

*Versión 1.0.0 - Enero 2024*