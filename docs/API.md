# Documentación de API

Este documento describe la integración con la API externa utilizada por el plugin "Ewo Location Services".

## Endpoints

### Búsqueda de ubicaciones por coordenadas

Este endpoint devuelve los servicios disponibles en una ubicación específica.

**URL**: `{base_url}/middleware/gps_cords/getServiceableLocationsByLatLng.php`

**Método**: POST (con fallback a GET)

**Headers**:

- `Content-Type`: application/json
- `api-key`: {api_key}

**Parámetros**:

```json
{
  "latitude": "40.740117894418454",
  "longitude": "-74.23119877930732",
  "requesting_system": "website"
}
```

**Respuesta exitosa**:

```json
{
  "serviceableLocations": [
    {
      "id": "123",
      "name": "Servicio Premium",
      "price": "99.99",
      "description": "Descripción del servicio premium",
      "type": "premium",
      "addons": [
        {
          "id": "a1",
          "name": "Addon 1",
          "price": "19.99"
        },
        {
          "id": "a2",
          "name": "Addon 2",
          "price": "29.99"
        }
      ]
    }
  ]
}
```

**Respuesta de error (404)**:

```json
{
  "error": "No serviceable locations found at these coordinates",
  "code": 404
}
```

### Creación de oportunidades

Este endpoint registra una nueva oportunidad de venta cuando un usuario se registra para un servicio.

**URL**: `{base_url}/opportunities.php`

**Método**: POST

**Headers**:

- `Content-Type`: application/json
- `api-key`: {api_key}

**Parámetros**:

```json
{
  "user_id": "123",
  "username": "usuario123",
  "email": "usuario@ejemplo.com",
  "first_name": "Nombre",
  "last_name": "Apellido",
  "service_id": "456",
  "addons": ["a1", "a2"]
}
```

**Respuesta exitosa**:

```json
{
  "success": true,
  "opportunity_id": "789",
  "message": "Opportunity created successfully"
}
```

## Autenticación

La API utiliza una clave API para la autenticación. Esta clave se puede configurar en el panel de administración del plugin. La clave API debe enviarse en los headers de la solicitud como `api-key`.

## Formatos de datos

- **Coordenadas**: Deben enviarse como valores numéricos (latitud y longitud)
- **IDs**: Deben enviarse como strings
- **Precios**: Se devuelven como strings con formato de número decimal

## Solución de problemas

### Error 404

Si recibes un error 404, considera lo siguiente:

1. Verifica que las coordenadas estén dentro de un área de servicio válida
2. Asegúrate de que la clave API sea correcta y esté en el formato adecuado
3. Verifica que la URL del endpoint sea correcta

### Problemas de autenticación

Si la API rechaza tu clave API, prueba los diferentes métodos de autenticación utilizando la herramienta "API Auth Tester" incluida en el plugin para determinar el formato correcto.

## Notas adicionales

- La API espera que las coordenadas estén en el formato decimal estándar (ej: 40.740117894418454, -74.23119877930732)
- El parámetro `requesting_system` debe ser siempre "website" para solicitudes desde este plugin
