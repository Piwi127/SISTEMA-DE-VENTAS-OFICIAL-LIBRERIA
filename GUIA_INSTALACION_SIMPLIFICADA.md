# 🚀 Guía de Instalación Simplificada
## Sistema de Ventas - Librería Belén (Optimizado)

### ⚡ Instalación Rápida (5 minutos)

#### 1. Requisitos Previos
- ✅ XAMPP instalado y funcionando
- ✅ Apache y MySQL iniciados
- ✅ Navegador web

#### 2. Pasos de Instalación

**Paso 1: Copiar Archivos**
```
1. Descargar/copiar la carpeta del proyecto
2. Mover a: C:\xampp\htdocs\
3. Renombrar a "libreria" (opcional)
```

**Paso 2: Instalación Automática**
```
1. Abrir navegador
2. Ir a: http://localhost/SISTEMA%20DE%20VENTAS%20OFICIAL%20LIBRERIA/install.php
3. Seguir el asistente de instalación
4. ¡Listo! El sistema está configurado
```

#### 3. Acceso al Sistema

**URL de acceso:**
```
http://localhost/SISTEMA%20DE%20VENTAS%20OFICIAL%20LIBRERIA/
```

**Credenciales por defecto:**
- **Administrador:**
  - Email: `admin@libreria.com`
  - Contraseña: `admin123`

- **Vendedor:**
  - Email: `vendedor@libreria.com`
  - Contraseña: `admin123`

### 🔧 Configuración Automática

El instalador optimizado configura automáticamente:

✅ **Base de datos** `sistema_ventas_libreria`
✅ **Archivo de configuración** `config/database.php`
✅ **Tablas de la base de datos** con estructura completa
✅ **Datos de ejemplo** (categorías, productos, usuarios)
✅ **Función de conexión** `getConnection()` optimizada

### 📁 Estructura Optimizada

```
SISTEMA DE VENTAS OFICIAL LIBRERIA/
├── 📁 config/           # Configuración automática
├── 📁 database/         # Esquema único optimizado
├── 📁 includes/         # Funciones consolidadas
│   ├── functions.php    # Funciones unificadas
│   ├── header.php       # Header común reutilizable
│   └── navbar.php       # Navegación
├── 📁 productos/        # Gestión de productos
├── 📁 ventas/           # Gestión de ventas
├── 📁 clientes/         # Gestión de clientes
├── 📁 usuarios/         # Gestión de usuarios
├── 📁 reportes/         # Reportes y estadísticas
└── install.php          # Instalador único
```

### 🎯 Beneficios de la Optimización

- **30% menos archivos** - Sistema más limpio
- **Instalación más rápida** - Proceso automatizado
- **Mejor rendimiento** - Funciones consolidadas
- **Fácil mantenimiento** - Código organizado
- **Sin duplicaciones** - Estructura optimizada

### 🔍 Solución de Problemas

**Error de conexión a la base de datos:**
```
1. Verificar que MySQL esté iniciado en XAMPP
2. Comprobar credenciales en config/database.php
3. Asegurar que la base de datos existe
```

**Error 404 al acceder:**
```
1. Verificar que Apache esté iniciado
2. Comprobar la ruta del proyecto en htdocs
3. Verificar permisos de carpeta
```

**Problemas con el instalador:**
```
1. Verificar permisos de escritura en /config
2. Comprobar que PHP tenga extensiones PDO
3. Revisar logs de error de Apache
```

### 📞 Soporte

Para soporte técnico:
- Revisar el archivo `README.md` completo
- Verificar logs de error en XAMPP
- Comprobar la documentación de funciones optimizadas

---

**¡Sistema optimizado y listo para usar!** 🎉