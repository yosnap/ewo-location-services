# Changelog - Ewo Location Services

## [2.0.0] - 2025-05-XX

### Major Release: Complete Refactor and New Architecture

**Version 2.0.0 is a major update that completely refactors the plugin, introducing a new modular architecture, improved user experience, and full compatibility with modern WordPress and browser standards.**

#### Key changes from v1 to v2:

- **Step-by-step navigation:** Each step (location, coverage, plans, addons, user, installation, confirmation) is now a separate page, mapped from the admin.
- **Shortcodes for everything:** All steps and conditional content are now managed via dedicated shortcodes, making the plugin much more flexible and easy to integrate.
- **Modern UI/UX:** New sliders/carousels for plans and addons, modals for user details, and unified, theme-proofed styles.
- **Admin improvements:** Centralized settings, color customization, endpoint mapping, and a live-updated shortcodes documentation tab.
- **Debug and logging:** Enhanced logging and a new API tester tool in the admin.
- **API integration:** All endpoints and payloads updated to the latest Wisper API structure, with improved error handling.
- **Security and compatibility:** All API keys and sensitive data are managed securely from the admin, never hardcoded.
- **Full English frontend and backend.**
- **Backward compatibility:** The old multistep form is no longer used; all flows are now modular and page-based.

#### Migration notes:

- **If you are upgrading from v1:**  
  - You must update your pages to use the new shortcodes for each step.
  - Review the "Page Mapping" tab in the admin to assign each step to a WordPress page.
  - The old multistep shortcode is deprecated and should be removed.
  - Review the new admin settings and shortcodes documentation for integration examples.

## [1.2.0] - 2025-05-20

### Added
- PIN Code field in checkout, auto-generated and editable by the user (required for opportunity creation).
- API key from plugin settings is now included in all opportunity API requests.
- All opportunity API responses are stored in localStorage and shown in the console on the Thank You page.

### Changed
- Opportunity is only created from the checkout, not from the addons modal.
- Unified ad_source options in all forms and modals for consistency.
- PIN Code only appears in checkout, not in modals.
- Unified and theme-proofed form and modal styles.

### Fixed
- Consistent ad_source values and correct API mapping in all forms.
- PIN Code field no longer appears in the addons modal.

### Docs
- Updated README with new shortcodes, modal usage, and troubleshooting section.

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
