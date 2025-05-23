# Ewo Location Services

Plugin de WordPress para servicios basados en ubicación, permitiendo a los usuarios encontrar y reservar servicios según su ubicación geográfica.

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

## Requisitos

- WordPress 5.0 o superior
- PHP 7.2 o superior
- Conexión a Internet para la carga de mapas y geocodificación

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
