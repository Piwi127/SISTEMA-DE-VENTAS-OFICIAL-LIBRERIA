@echo off
echo ========================================
echo    SISTEMA DE VENTAS - LIBRERIA BELEN
echo ========================================
echo.
echo Iniciando servidor PHP...
echo.

REM Cambiar al directorio del sistema
cd /d "%~dp0"

REM Verificar si XAMPP está instalado
if not exist "C:\xampp\php\php.exe" (
    echo ERROR: No se encontró XAMPP en C:\xampp\
    echo Por favor, instale XAMPP o ajuste la ruta en este archivo.
    pause
    exit /b 1
)

REM Mostrar información del sistema
echo Directorio del sistema: %CD%
echo Servidor PHP: C:\xampp\php\php.exe
echo Puerto: 8000
echo.

REM Iniciar el servidor PHP en segundo plano
echo Iniciando servidor en http://localhost:8000...
start "Servidor PHP - Sistema de Ventas" C:\xampp\php\php.exe -S localhost:8000

REM Esperar un momento para que el servidor inicie
echo Esperando que el servidor inicie...
timeout /t 3 /nobreak >nul

REM Abrir el navegador con la página de login
echo Abriendo navegador...
start "" "http://localhost:8000/login.php"

echo.
echo ========================================
echo Sistema iniciado correctamente!
echo ========================================
echo.
echo URLs disponibles:
echo - Login: http://localhost:8000/login.php
echo - Dashboard: http://localhost:8000/index.php
echo - Productos: http://localhost:8000/productos/lista_productos.php
echo - Ventas: http://localhost:8000/ventas/nueva_venta.php
echo - Clientes: http://localhost:8000/clientes/lista_clientes.php
echo - Usuarios: http://localhost:8000/usuarios/lista_usuarios.php
echo - Reportes: http://localhost:8000/reportes/reportes.php
echo.
echo Credenciales por defecto:
echo - Admin: admin / admin123
echo - Vendedor: vendedor / vendedor123
echo.
echo Para detener el servidor, cierre esta ventana o presione Ctrl+C
echo.
pause