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
- **Servicios y addons se mostrarán en sliders/carouseles modernos, sin filtros ni paginación.**
- **En el paso 2 se usará el endpoint `getServiceabilityDetails` (no `getServiceableLocationsByLatLng`), que devuelve un `coverage_code` para el paso 3.**
- **El paso 3 usará el `coverage_code` para llamar a `getPackages` y mostrar los planes.**
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
- **En la v2, los servicios y addons se mostrarán en sliders/carouseles, sin filtros ni paginación.**
- **En la v2, el flujo será por páginas separadas y el coverage_code se obtiene con getServiceabilityDetails.**

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

## Plan de acción técnico – v2

### Justificación y contexto
- **v1**: Plugin con flujo multistep en una sola página, lógica monolítica, integración con APIs externas, UI moderna, logging y documentación.
- **v2**: Se busca desacoplar el flujo, mejorar la mantenibilidad, UX y escalabilidad, siguiendo las nuevas directrices y reglas del proyecto.

### Cambios principales v2
- Cada paso del flujo será una página/vista independiente (no multistep en una sola página).
- Servicios y addons se mostrarán en sliders/carouseles modernos, eliminando filtros y paginación.
- El endpoint principal para el paso 2 será `getServiceabilityDetails` (no `getServiceableLocationsByLatLng`).
- El `coverage_code` obtenido en el paso 2 se usará en el paso 3 para llamar a `getPackages`.
- El formulario de usuario y la selección de addons se mostrarán en un modal.
- Se mantiene toda la funcionalidad, pero con navegación desacoplada y arquitectura modular.
- Se actualiza la documentación y el roadmap para reflejar estos cambios.

### Arquitectura propuesta

**Estructura de carpetas**

```
ewo-location-services/
│
├── admin/           # Configuración, logs, herramientas de prueba
├── frontend/        # JS, CSS, vistas, templates
├── includes/        # Clases y funciones PHP reutilizables
├── templates/       # Archivos PHP para renderizado de vistas
├── logs/            # Archivos de log (asegurar permisos)
├── README.md
├── CHANGELOG.md
└── ewo-location-services.php  # Archivo principal del plugin
```

**Flujo de usuario (v2)**
1. Página 1: Selección de ubicación (input, geolocalización, mapa)
2. Página 2: Llamada a `getServiceabilityDetails` → obtención de `coverage_code`
3. Página 3: Llamada a `getPackages` usando el `coverage_code` → selección de plan
4. Modal: Formulario de usuario y selección de addons (sliders/carouseles)
5. Resumen/Confirmación: Visualización de datos, descarga/impresión, envío a API

### Pasos de desarrollo

1. Refactorizar navegación a páginas/vistas separadas
2. Implementar sliders/carouseles para servicios y addons
3. Adaptar integración API a los nuevos endpoints y flujo (`getServiceabilityDetails` → `getPackages`)
4. Implementar modal para formulario de usuario y addons
5. Mejorar logging y UI de depuración
6. Actualizar documentación técnica y de usuario
7. Pruebas de integración y compatibilidad
8. Validar seguridad y sanitización de datos
9. Revisar y actualizar el roadmap

### Notas técnicas y buenas prácticas
- El plugin debe estar completamente en inglés en frontend y backend.
- Usar la Settings API de WordPress para la configuración.
- Usar AJAX para interacciones dinámicas.
- Seguir los estándares de codificación de WordPress (PHP, JS, CSS).
- Prefijar todas las funciones, clases y hooks.
- Documentar exhaustivamente el código y los flujos.
- Proveer ejemplos de configuración y uso en el README.
- Mantener el CHANGELOG actualizado con cada release.
- Asegurar compatibilidad con versiones recientes de WordPress y navegadores modernos.

### Historial y contexto para nuevos desarrolladores
- El plugin evolucionó de un flujo multistep monolítico (v1) a una arquitectura desacoplada y modular (v2).
- Se prioriza la mantenibilidad, la experiencia de usuario y la facilidad de integración con APIs externas.
- Toda la lógica de negocio y los endpoints están documentados en el README y CHANGELOG.
- El roadmap y las tareas pendientes están claramente definidos para facilitar la continuidad del desarrollo.

## Referencias de diseño

- Los mockups y el diseño visual del flujo v2 están disponibles en Figma: [Wisper ISP – Mockup (Figma)](https://www.figma.com/design/NoTWJ3zJCU8SJaYSznKG7N/Wisper-ISP---Mockup?node-id=5-489&t=tGDuzeF83RJ0d02i-0)

## Ejemplos de payloads y estructura de endpoints

### Endpoint: getServiceabilityDetails

**URL (dev):**
```
POST http://dev-middleware.wisperisp.com/middleware/gps_cords/getServiceabilityDetails.php
```

**Payload de solicitud (form-data):**
```json
{
  "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
  "latitude": "38.515800131698555",
  "longitude": "-89.80763640199196",
  "requesting_system": "website"
}
```

**Descripción de campos:**
- `api-key` (string, requerido): API key de autenticación.
- `latitude` (string/float, requerido): Latitud de la ubicación a consultar.
- `longitude` (string/float, requerido): Longitud de la ubicación a consultar.
- `requesting_system` (string, requerido): Identificador del sistema que realiza la petición (ej: "website").

**Ejemplo de respuesta:**
```json
{
    "endpoint": "getServiceabilityDetails",
    "object": "gps_cord",
    "success": true,
    "request_origin": "http://localhost:8100",
    "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
    "input-parameters": {
        "latitude": "38.515800131698555",
        "longitude": "-89.80763640199196",
        "requesting_system": "website"
    },
    "return-data": {
        "serviceability-info": {
            "is_serviceable": true,
            "coverage_code": "20100102300",
            "network_type": "Non-Tarana",
            "government_program": "Non-CAF",
            "coverage_type": "No Cellular Coverage",
            "fiber_coverage_type": "No Fiber Available",
            "minimum_cpe_height_in_ft": 23,
            "coverage_confidence": {
                "status_text_full": "GREEN (YES) – High Confidence",
                "status_color": "Green",
                "status_color_hex": "#7bbd35",
                "coverage_status": "YES",
                "coverage_status_text": "High Confidence"
            },
            "coverage_confidence_score": 1,
            "max_download_speed_mbps": 10,
            "location_lat": 38.51586881229582,
            "location_lng": -89.80760253882613
        }
    },
    "code": 200,
    "timestamp": "2025-05-19",
    "start_timestamp": "2025-05-19 06:58:40.689",
    "end_timestamp": "2025-05-19 06:58:41.878"
}
```

**Campos relevantes en la respuesta:**
- `success`: Indica si la consulta fue exitosa.
- `return-data.serviceability-info.is_serviceable`: Indica si la ubicación es servible.
- `return-data.serviceability-info.coverage_code`: Código de cobertura, necesario para el siguiente paso (getPackages).
- `coverage_confidence`: Información sobre la confianza de la cobertura (color, texto, score).
- `max_download_speed_mbps`: Velocidad máxima estimada.
- `location_lat`, `location_lng`: Coordenadas ajustadas por el sistema.

### Endpoint: getPackages

**URL (dev):**
```
POST https://dev-middleware.wisperisp.com/middleware/package/getPackages.php
```

**Payload de solicitud (form-data):**
```json
{
  "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
  "latitude": "37.806479687628936",
  "longitude": "-89.07903653069094",
  "requesting_system": "website",
  "coverage_code": "20101003503"
}
```

**Descripción de campos:**
- `api-key` (string, requerido): API key de autenticación.
- `latitude` (string/float, requerido): Latitud de la ubicación.
- `longitude` (string/float, requerido): Longitud de la ubicación.
- `requesting_system` (string, requerido): Identificador del sistema (ej: "website").
- `coverage_code` (string, requerido): Código de cobertura obtenido en el paso anterior (getServiceabilityDetails).

**Ejemplo de respuesta:**
```json
{
    "endpoint": "getPackages",
    "object": "package",
    "success": true,
    "request_origin": "http://localhost:8100",
    "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
    "input-parameters": {
        "requesting_system": "website",
        "latitude": "37.806479687628936",
        "longitude": "-89.07903653069094",
        "coverage_code": "20101003503"
    },
    "return-data": {
        "package-data": [
            {
                "plan_id": 69,
                "plan_name": "HZ 100MB",
                "price": "89.99",
                "price_with_auto_pay_discount_applied": "84.99",
                "speed_upload_mbs": 34.5,
                "speed_download_mbs": 115,
                "type": "internet_service",
                "readable_type": "Internet",
                "display_as_addon": false,
                "plan_description": "",
                "display_on_customers_with_type": ["Residential"]
            },
            // ... más planes y addons ...
            {
                "plan_id": 27,
                "plan_name": "Equipment Fee",
                "price": "7.00",
                "type": "recurring_service",
                "readable_type": "Recurring",
                "display_as_addon": true,
                "plan_description": "This is recommended since if you don't use our equipment then you might be charged for service visits.",
                "display_on_customers_with_type": ["Residential"]
            }
        ]
    },
    "code": 200,
    "timestamp": "2025-05-19",
    "start_timestamp": "2025-05-19 06:57:14.954",
    "end_timestamp": "2025-05-19 06:57:16.982"
}
```

**Campos relevantes en la respuesta:**
- `success`: Indica si la consulta fue exitosa.
- `return-data.package-data`: Array de planes y addons disponibles para la ubicación y cobertura consultada.
    - `plan_id`: ID único del plan o addon.
    - `plan_name`: Nombre comercial del plan.
    - `price`: Precio estándar.
    - `price_with_auto_pay_discount_applied`: Precio con descuento por pago automático (si aplica).
    - `speed_upload_mbs`, `speed_download_mbs`: Velocidades de subida/bajada (solo para planes de internet).
    - `type`: Tipo de servicio (internet_service, recurring_service, voice_service, etc.).
    - `readable_type`: Tipo legible para mostrar en UI.
    - `display_as_addon`: Booleano, indica si es un addon/cross-sell.
    - `plan_description`: Descripción del plan.
    - `display_on_customers_with_type`: Tipos de cliente a los que se muestra (ej: Residential).

### Endpoint: createCustomer

**URL (dev):**
```
POST http://dev-middleware.wisperisp.com/middleware/customer/createCustomer.php
```

**Payload de solicitud (form-data):**
```json
{
  "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
  "first_name": "Paulo",
  "last_name": "Smith",
  "type": "person",
  "subtype": "residential",
  "company_name": "Wisper ISP",
  "mobile_number": "6189645854",
  "email": "test@test.com",
  "address_line_one": "200 4th St.",
  "city": "Colp",
  "state": "IL",
  "zip": "62921",
  "ad_source": "local_event",
  "added_by": "Julian Smith",
  "support_pin": "1234",
  "status": "new",
  "address_line_two": "",
  "lat": 37.806298005304,
  "lng": -89.078962993379,
  "text_messages_for_operational_alerts": "Yes",
  "email_messages_for_operational_alerts": "Yes",
  "email_messages_for_wisper_news": "No",
  "subscribe_to_text_payments": "Yes"
}
```

**Descripción de campos principales:**
- `api-key` (string, requerido): API key de autenticación.
- `first_name`, `last_name` (string, requerido): Nombre y apellido del cliente.
- `type` (string, requerido): Tipo de cliente (ej: "person").
- `subtype` (string, requerido): Subtipo (ej: "residential").
- `company_name` (string, opcional): Nombre de la empresa (si aplica).
- `mobile_number` (string, requerido): Teléfono móvil.
- `email` (string, requerido): Correo electrónico.
- `address_line_one`, `address_line_two` (string, requerido/opcional): Dirección principal y secundaria.
- `city`, `state`, `zip` (string, requerido): Ciudad, estado y código postal.
- `ad_source` (string, opcional): Fuente de adquisición.
- `added_by` (string, opcional): Nombre del agente o sistema que añade el cliente.
- `support_pin` (string, requerido): PIN de soporte.
- `status` (string, opcional): Estado inicial del cliente (ej: "new").
- `lat`, `lng` (float, opcional): Coordenadas geográficas.
- `text_messages_for_operational_alerts`, `email_messages_for_operational_alerts`, `email_messages_for_wisper_news`, `subscribe_to_text_payments` (string, opcional): Preferencias de comunicación ("Yes"/"No").

**Ejemplo de respuesta:**
```json
{
    "endpoint": "createCustomer",
    "object": "customer",
    "success": true,
    "request_origin": "http://localhost:8100",
    "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
    "input-parameters": {
        "first_name": "Paulo",
        "last_name": "Smith",
        "mobile_number": "6189645854",
        "email": "test@test.com",
        "address_line_one": "200 4th St.",
        "city": "Colp",
        "state": "IL",
        "zip": "62921",
        "support_pin": "1234",
        "type": "person",
        "subtype": "residential",
        "ad_source": "local_event",
        "added_by": "Julian Smith",
        "company_name": "Wisper ISP",
        "status": "new",
        "address_line_two": "",
        "lat": 37.806298005304,
        "lng": -89.078962993379,
        "text_messages_for_operational_alerts": "Yes",
        "email_messages_for_operational_alerts": "Yes",
        "email_messages_for_wisper_news": "No",
        "subscribe_to_text_payments": "Yes",
        "norm_account_id": 69092,
        "splynx_customer_id": 20814
    },
    "return-data": {
        "norm_account_id": 69092,
        "splynx_customer_id": 20814,
        "norm_account_and_splynx_customer_linked": true,
        "tags_imported": false,
        "geo_tags_synced": true,
        "norm_account_url": "https://dev-single-norm.wisperisp.com/norm/accounts/accountdetails.php?id=69092",
        "splynx_customer_link": "https://dev-portal.wisperisp.com/admin/customers/view?id=20814"
    },
    "code": 200,
    "timestamp": "2025-05-19",
    "start_timestamp": "2025-05-19 09:25:38.280",
    "end_timestamp": "2025-05-19 09:25:42.996"
}
```

**Campos relevantes en la respuesta:**
- `success`: Indica si la creación fue exitosa.
- `return-data.norm_account_id`, `return-data.splynx_customer_id`: IDs generados en los sistemas externos.
- `norm_account_and_splynx_customer_linked`: Indica si la vinculación fue exitosa.
- `norm_account_url`, `splynx_customer_link`: Enlaces directos a los registros creados en los sistemas externos.

### Endpoint: createOpportunity

**URL (dev):**
```
POST http://dev-middleware.wisperisp.com/middleware/opportunity/createOpportunity.php
```

**Payload de solicitud (form-data):**

Campos principales (además de los datos de contacto y dirección):
- `api-key` (string, requerido)
- `address_line_one`, `address_line_two`, `city`, `state`, `zip` (string, requerido)
- `latitude`, `longitude` (string/float, requerido)
- `first_name`, `last_name`, `email`, `mobile_number` (string, requerido)
- `ad_source`, `customer_type`, `customer_subtype` (string, requerido)
- `contact_source` (string, opcional)
- `pd_building_color`, `pd_roof_type`, `pd_house_style`, `pd_rent_own`, `pd_install_comment` (string, opcional)
- `support_pin`, `status` (string/int, requerido)
- `text_messages_for_operational_alerts`, `email_messages_for_operational_alerts`, `email_messages_for_wisper_news`, `subscribe_to_text_payments` (string/bool, requerido)
- **Campos JSON enviados como texto:**
    - `serviceability_results_json`
    - `selected_internet_plans_json`
    - `selected_recurring_addons_json`
    - `selected_voice_addons_json`
    - `tags_json`

**Ejemplo de payload para campos JSON:**

`serviceability_results_json`:
```json
[
  {
    "is_serviceable": true,
    "coverage_code": 1010100350,
    "network_type": "Tarana",
    "government_program": "Non-CAF",
    "coverage_type": "No Cellular Coverage",
    "coverage_confidence": {
      "status_text_full": "GREEN (YES) – High Confidence",
      "status_color": "Green",
      "status_color_hex": "#7bbd35",
      "coverage_status": "YES",
      "coverage_status_text": "High Confidence"
    },
    "coverage_confidence_score": 1,
    "max_download_speed_mbps": 100,
    "minimum_cpe_height_in_ft": 35,
    "coverage_calculation_method": "Coverage on Building"
  }
]
```

`selected_internet_plans_json`:
```json
[
  {
    "plan_id": 17,
    "plan_name": "Wisper 25Mb",
    "price": "80.00",
    "speed_upload_mbs": 5,
    "speed_download_mbs": 25,
    "type": "internet_service",
    "readable_type": "Internet",
    "display_as_addon": false,
    "plan_description": "This is a test plan description"
  }
]
```

`selected_recurring_addons_json`:
```json
[
  {
    "plan_id": 27,
    "plan_name": "Equipment Fee",
    "price": "7.00",
    "type": "recurring_service",
    "readable_type": "Recurring",
    "display_as_addon": true,
    "plan_description": "This is recommended since if you don't use our equipment then you might be charged for service visits."
  },
  {
    "plan_id": 29,
    "plan_name": "Equipment Fee (Mesh)",
    "price": "7.00",
    "type": "recurring_service",
    "readable_type": "Recurring",
    "display_as_addon": true,
    "plan_description": "This is if you want a mesh extender in addition to your wisper provided router"
  }
]
```

`selected_voice_addons_json`:
```json
[
  {
    "plan_id": 1,
    "plan_name": "Residential Bundled VoIP",
    "price": "25.00",
    "type": "voice_service",
    "readable_type": "Voice",
    "display_as_addon": true,
    "plan_description": "This is the plan if you want VOIP with an Internet Plan"
  }
]
```

**Ejemplo de respuesta:**
```json
{
    "endpoint": "createOpportunity",
    "object": "opportunity",
    "success": true,
    "request_origin": "http://localhost:8100",
    "api-key": "8849ee-dccb0d-2a3c5b-3fcdcf-925c8a",
    "input-parameters": {
        // ... todos los campos enviados, incluyendo los JSON ...
    },
    "return-data": {
        "update_opportunity_request": {
            // ... información detallada de la oportunidad creada ...
        },
        "opportunity-data": {
            // ... datos principales de la oportunidad ...
        }
    },
    "code": 200,
    "timestamp": "2025-05-19",
    "start_timestamp": "2025-05-19 00:21:24.295",
    "end_timestamp": "2025-05-19 00:21:25.724"
}
```

**Notas:**
- Los campos JSON deben enviarse como texto plano (string) en el form-data.
- La respuesta incluye tanto los datos enviados como la información de la oportunidad creada, con IDs, campos formateados y relaciones.
- Es importante validar que los JSON enviados sean válidos y coincidan con la estructura esperada por el backend.
