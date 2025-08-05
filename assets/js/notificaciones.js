// Sistema de notificaciones en tiempo real
class SistemaNotificaciones {
    constructor() {
        this.intervalo = null;
        this.notificacionesActivas = new Set();
        this.inicializar();
    }

    inicializar() {
        // Verificar notificaciones cada 30 segundos
        this.intervalo = setInterval(() => {
            this.verificarNotificaciones();
        }, 30000);

        // Verificar inmediatamente al cargar
        this.verificarNotificaciones();

        // Crear contenedor de notificaciones si no existe
        this.crearContenedorNotificaciones();
    }

    crearContenedorNotificaciones() {
        if (!document.getElementById('notificaciones-container')) {
            const container = document.createElement('div');
            container.id = 'notificaciones-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
    }

    async verificarNotificaciones() {
        try {
            // Determinar la ruta correcta basada en la ubicación actual
            let rutaNotificaciones = 'includes/notificaciones.php';
            if (window.location.pathname.includes('/notas/') || 
                window.location.pathname.includes('/productos/') || 
                window.location.pathname.includes('/clientes/') || 
                window.location.pathname.includes('/ventas/') || 
                window.location.pathname.includes('/usuarios/') || 
                window.location.pathname.includes('/reportes/')) {
                rutaNotificaciones = '../includes/notificaciones.php';
            }
            
            const response = await fetch(rutaNotificaciones + '?action=obtener_notificaciones');
            if (!response.ok) return;

            const notificaciones = await response.json();
            
            notificaciones.forEach(notificacion => {
                if (!this.notificacionesActivas.has(notificacion.id)) {
                    this.mostrarNotificacion(notificacion);
                    this.notificacionesActivas.add(notificacion.id);
                }
            });
        } catch (error) {
            console.error('Error al verificar notificaciones:', error);
        }
    }

    mostrarNotificacion(notificacion) {
        const container = document.getElementById('notificaciones-container');
        
        const notifElement = document.createElement('div');
        notifElement.className = 'alert alert-warning alert-dismissible fade show notification-item';
        notifElement.style.cssText = `
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #ffc107;
        `;
        
        const fechaFormateada = new Date(notificacion.fecha_recordatorio).toLocaleString('es-ES');
        
        notifElement.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-bell text-warning me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">
                        <i class="fas fa-sticky-note"></i> Recordatorio de Nota
                    </h6>
                    <strong>${this.escapeHtml(notificacion.asunto)}</strong><br>
                    <small class="text-muted">${this.escapeHtml(notificacion.cuerpo_mensaje.substring(0, 100))}${notificacion.cuerpo_mensaje.length > 100 ? '...' : ''}</small><br>
                    <small class="text-info"><i class="fas fa-clock"></i> ${fechaFormateada}</small>
                </div>
                <div class="ms-2">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="sistemaNotificaciones.verNota(${notificacion.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="sistemaNotificaciones.marcarVista(${notificacion.id}, this.closest('.notification-item'))">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(notifElement);
        
        // Auto-ocultar después de 10 segundos si no se interactúa
        setTimeout(() => {
            if (notifElement.parentNode) {
                this.ocultarNotificacion(notifElement, notificacion.id);
            }
        }, 10000);
        
        // Reproducir sonido de notificación
        this.reproducirSonidoNotificacion();
    }

    async marcarVista(notaId, elemento) {
        try {
            // Determinar la ruta correcta basada en la ubicación actual
            let rutaNotificaciones = 'includes/notificaciones.php';
            if (window.location.pathname.includes('/notas/') || 
                window.location.pathname.includes('/productos/') || 
                window.location.pathname.includes('/clientes/') || 
                window.location.pathname.includes('/ventas/') || 
                window.location.pathname.includes('/usuarios/') || 
                window.location.pathname.includes('/reportes/')) {
                rutaNotificaciones = '../includes/notificaciones.php';
            }
            
            const formData = new FormData();
            formData.append('action', 'marcar_vista');
            formData.append('nota_id', notaId);
            
            const response = await fetch(rutaNotificaciones, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                this.ocultarNotificacion(elemento, notaId);
            }
        } catch (error) {
            console.error('Error al marcar notificación como vista:', error);
        }
    }

    verNota(notaId) {
        // Determinar la ruta correcta basada en la ubicación actual
        let rutaNotas = 'notas/lista_notas.php';
        if (window.location.pathname.includes('/notas/') || 
            window.location.pathname.includes('/productos/') || 
            window.location.pathname.includes('/clientes/') || 
            window.location.pathname.includes('/ventas/') || 
            window.location.pathname.includes('/usuarios/') || 
            window.location.pathname.includes('/reportes/')) {
            rutaNotas = '../notas/lista_notas.php';
        }
        
        // Redirigir a la página de notas con el ID específico
        window.location.href = `${rutaNotas}?highlight=${notaId}`;
    }

    ocultarNotificacion(elemento, notaId) {
        elemento.style.transition = 'opacity 0.3s ease';
        elemento.style.opacity = '0';
        
        setTimeout(() => {
            if (elemento.parentNode) {
                elemento.parentNode.removeChild(elemento);
            }
            this.notificacionesActivas.delete(notaId);
        }, 300);
    }

    reproducirSonidoNotificacion() {
        // Crear un sonido simple usando Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (error) {
            console.log('No se pudo reproducir el sonido de notificación');
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    destruir() {
        if (this.intervalo) {
            clearInterval(this.intervalo);
        }
    }
}

// Inicializar el sistema de notificaciones cuando el DOM esté listo
let sistemaNotificaciones;

document.addEventListener('DOMContentLoaded', function() {
    sistemaNotificaciones = new SistemaNotificaciones();
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', function() {
    if (sistemaNotificaciones) {
        sistemaNotificaciones.destruir();
    }
});