# Guía de instalación y uso

Esta guía proporciona instrucciones detalladas para instalar, configurar y utilizar el plugin "Ewo Location Services" para WordPress.

## Requisitos previos

- WordPress 5.6 o superior
- PHP 7.4 o superior
- Permisos de escritura en el directorio `logs` del plugin
- Clave API válida para el servicio de ubicación

## Instalación

1. **Descargar el plugin**

   - Descarga el archivo ZIP del plugin desde el repositorio

2. **Instalar el plugin en WordPress**

   - Ve a tu panel de administración de WordPress
   - Navega a Plugins > Añadir nuevo
   - Haz clic en "Subir plugin" y selecciona el archivo ZIP descargado
   - Haz clic en "Instalar ahora"

3. **Activar el plugin**
   - Una vez instalado, haz clic en "Activar plugin"
   - El plugin ahora aparecerá en el menú lateral como "Ewo Location Services"

## Configuración

1. **Acceder a la configuración**

   - Ve a Ewo Location Services > Configuración en el menú de administración

2. **Configurar el entorno de API**

   - Selecciona "Desarrollo" o "Producción" según el entorno que desees utilizar

3. **Configurar las URLs de API**

   - Introduce las URLs de los endpoints para:
     - Búsqueda de ubicaciones por coordenadas (dev y prod)
     - Oportunidades (dev y prod)

4. **Configurar la clave API**

   - Introduce tu clave API en el campo correspondiente
   - Esta clave se utilizará para la autenticación con el servicio externo

5. **Guardar la configuración**
   - Haz clic en "Guardar cambios" para aplicar la configuración

## Uso

### Añadir el formulario a una página

1. **Crear o editar una página**

   - Ve a Páginas > Añadir nueva o edita una página existente

2. **Insertar el shortcode**

   - Añade el siguiente shortcode donde quieras que aparezca el formulario:

   ```
   [ewo_location_form]
   ```

3. **Personalizar el formulario (opcional)**
   - El shortcode acepta parámetros opcionales:
   ```
   [ewo_location_form title="Encuentra servicios en tu zona" button_text="Buscar"]
   ```

### Flujo de uso para los visitantes

1. **Introducir ubicación**

   - Los usuarios pueden introducir su ubicación de tres formas:
     - Escribiendo una dirección en el campo de texto
     - Haciendo clic en "Usar mi ubicación" para usar la geolocalización del navegador
     - Seleccionando un punto en el mapa interactivo

2. **Buscar servicios**

   - Después de especificar la ubicación, el usuario hace clic en "Buscar"
   - El sistema envía las coordenadas a la API y muestra los servicios disponibles

3. **Seleccionar un servicio**

   - El usuario elige uno de los servicios disponibles
   - Si hay opciones adicionales (addons), se muestran a continuación

4. **Introducir datos personales**
   - El usuario rellena el formulario con sus datos personales
   - Al enviar el formulario, se crea una cuenta de usuario en WordPress
   - Los datos se envían también al endpoint de oportunidades de la API

## Herramientas de depuración

### Logs del sistema

1. **Acceder a los logs**

   - Ve a Ewo Location Services > Logs en el menú de administración
   - Aquí puedes ver un registro de todas las operaciones importantes

2. **Filtrar logs**

   - Puedes filtrar los logs por nivel de severidad (INFO, WARNING, ERROR)
   - También puedes buscar entradas específicas

3. **Gestionar logs**
   - Utiliza los botones disponibles para descargar o limpiar los logs

### Probador de API

1. **Acceder al probador**

   - Ve a Ewo Location Services > API Tester en el menú de administración

2. **Realizar pruebas**
   - Selecciona el endpoint que deseas probar
   - Introduce las coordenadas para la prueba
   - Haz clic en "Probar API" para ver la respuesta

### Probador de autenticación API

1. **Acceder al probador de autenticación**

   - Ve a Ewo Location Services > API Auth Tester en el menú de administración

2. **Probar diferentes métodos de autenticación**

   - Selecciona el método que deseas probar (POST/GET, ubicación de la API key)
   - Selecciona el formato de la API key
   - Haz clic en "Probar conexión API" para ver la respuesta

3. **Interpretar resultados**
   - El sistema analizará la respuesta y te mostrará recomendaciones
   - Si encuentra un método que funciona, te mostrará el código a implementar

## Solución de problemas comunes

### La API devuelve error 404

1. **Verificar las coordenadas**

   - Asegúrate de que las coordenadas estén en un área donde haya servicios disponibles

2. **Comprobar la configuración de la API**

   - Verifica que la URL del endpoint sea correcta
   - Usa el "API Auth Tester" para probar diferentes métodos de autenticación

3. **Revisar los logs**
   - Consulta los logs del sistema para ver más detalles sobre el error

### El mapa no se carga correctamente

1. **Verificar la conexión a internet**

   - El mapa requiere conexión a internet para cargar los tiles de OpenStreetMap

2. **Comprobar la consola del navegador**

   - Revisa si hay errores en la consola relacionados con el cargado del mapa

3. **Verificar conflictos con otros plugins**
   - Desactiva temporalmente otros plugins para ver si alguno está causando conflictos

### Problemas de registro de usuario

1. **Verificar los permisos de WordPress**

   - Asegúrate de que el plugin tenga permisos para crear usuarios

2. **Comprobar la validez de los datos**

   - El correo electrónico debe ser válido y no estar ya registrado
   - El nombre de usuario debe ser único

3. **Revisar los logs de error**
   - Consulta los logs para ver detalles específicos sobre el fallo de registro
