# Changelog - Ewo Location Services

## [1.1.0] - 2025-05-17

### Añadido

- Herramienta de prueba de API en el panel de administración
- Implementación de múltiples métodos de autenticación para API
- Modal de depuración mejorado con pestañas para diferentes vistas
- Análisis automático de estructura de respuesta API

### Corregido

- Formato de API key en solicitudes (cambio de `api_key` a `api-key`)
- Procesamiento correcto de diferentes estructuras de respuesta API
- Visualización adecuada de errores API en el frontend
- Manejo mejorado de errores 401 y 404 de la API
- Codificación correct de JSON en solicitudes POST

### Cambiado

- Eliminación de campos de URL redundantes en la configuración
- Mejora en la gestión de dependencias JavaScript
- Optimización del código JavaScript del frontend

## [1.0.0] - Primera versión estable

### Added
- Estructura modular de frontend (JS/CSS) y backend (PHP).
- Multistep form con pasos: ubicación, servicio, plan, addons, usuario, confirmación.
- Integración con APIs externas para servicios y oportunidades.
- Selección visual de cards para servicios, planes y addons.
- Guardado y recuperación de datos en localStorage.
- Resumen final con impresión y descarga.
- Modal de depuración para admins.
- Configuración flexible desde el admin de WordPress.
- Soporte para múltiples proveedores de mapas/autocompletado.
- Estilos modernos y responsive.
- Validaciones y UX amigable.
- Logging y debug para admins.
