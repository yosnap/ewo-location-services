# EWO Location Services Plugin

Plugin de WordPress para búsqueda y contratación de servicios según ubicación, con integración a APIs externas y flujo multistep.

## Características principales (v1)
- Formulario multistep en una sola página (location → servicios → planes → addons → usuario → confirmación).
- Selección de ubicación: dirección, geolocalización o mapa.
- Búsqueda de servicios según lat/lng vía API externa.
- Selección visual de cards para servicios, planes y addons.
- Formulario de usuario y envío de oportunidad a API externa.
- Resumen final con descarga/impresión y guardado en localStorage.
- Modal de depuración para admins.
- Configuración desde el backend: API keys, endpoints, opciones de UI.
- Frontend moderno y responsive.
- Todo el flujo en inglés.

## Requisitos
- WordPress reciente
- PHP 7.4+
- API externa de servicios y oportunidades

## Roadmap v2
- Separar cada paso en páginas independientes (no multiform).
- Modal para datos de usuario y selección de addons.
- Mejorar arquitectura para integración y mantenimiento.
- Mantener toda la funcionalidad, pero con navegación desacoplada.
- Mejorar logging, UX y compatibilidad.

## Descripción

Ewo Location Services es un plugin que integra la geocodificación y geolocalización con servicios externos API para ofrecer a los usuarios servicios disponibles en su ubicación. El plugin utiliza OpenStreetMap y Leaflet para la visualización de mapas y permite a los usuarios buscar servicios disponibles en una ubicación específica.

## Características principales

- Búsqueda de servicios por ubicación usando un mapa interactivo
- Geocodificación de direcciones y geolocalización del navegador
- Integración con APIs externas para obtener servicios disponibles
- Selección de servicios y opciones adicionales
- Registro de usuarios y envío de datos de oportunidad a API externa
- Panel de administración para configuración de endpoints API
- Sistema de logs para depuración y diagnóstico
- Herramienta de prueba de API en el panel de administración
- Modal de depuración para desarrolladores

## Instalación

1. Sube la carpeta `ewo-location-services` al directorio `/wp-content/plugins/`
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. Configura las opciones de API en el menú 'Location Services' del panel de administración
4. Coloca el shortcode `[ewo_location_services]` en cualquier página o entrada donde quieras mostrar el formulario de búsqueda de servicios

## Configuración

### Endpoints API

El plugin requiere la configuración de varios endpoints API:

1. **Endpoints de Serviceability**:

   - Development getServiceabilityDetails URL
   - Production getServiceabilityDetails URL
   - Development getServiceableLocationsByLatLng URL
   - Production getServiceableLocationsByLatLng URL

2. **Endpoints de Packages**:

   - Development getPackages URL
   - Production getPackages URL
   - Development getDiscountTemplates URL
   - Production getDiscountTemplates URL
   - Development addPackageToParent URL
   - Production addPackageToParent URL

3. **Endpoints de Customer**:

   - Development createCustomer URL
   - Production createCustomer URL
   - Development updateCustomer URL
   - Production updateCustomer URL
   - Development createCustomerComment URL
   - Production createCustomerComment URL

4. **Endpoints de Opportunity**:
   - Development createOpportunity URL
   - Production createOpportunity URL

### API Key

Se debe configurar una API key válida para autenticación con los endpoints.

## Uso

1. Los visitantes pueden buscar servicios usando un mapa interactivo o ingresando una dirección
2. Pueden seleccionar servicios disponibles en su ubicación
3. Seleccionar servicios adicionales (si están disponibles)
4. Completar un formulario de registro para reservar el servicio

## Herramientas de desarrollador

### API Tester

El plugin incluye una herramienta de prueba de API en el panel de administración que permite:

- Seleccionar endpoints específicos
- Ingresar coordenadas manualmente
- Ver la respuesta completa de la API
- Probar diferentes métodos de autenticación

### Sistema de Logs

El plugin mantiene un registro detallado de las interacciones con la API y errores, accesible desde el panel de administración.

## Soporte

Para soporte, contáctanos a través de [hola@sn4p.dev](mailto:hola@sn4p.dev).

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.
