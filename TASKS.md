# Tareas y planificación – EWO Location Services

## v2 – Checklist de desarrollo

### Paso 1: Search for Coverage (Selección de localización)
- [ ] Implementar pantalla "Search for Coverage" como primer paso del flujo.
- [ ] Input de dirección con autocomplete (sugerencias de direcciones mientras el usuario escribe).
- [ ] Icono de geolocalización dentro del input: al hacer click, pedir permisos al navegador y obtener la ubicación del usuario.
- [ ] Centrar el mapa en la dirección seleccionada (por autocomplete o geolocalización).
- [ ] Permitir al usuario hacer click en el mapa para seleccionar un punto manualmente.
- [ ] Al hacer pin en el mapa, abrir modal mostrando la dirección seleccionada y un botón para confirmar.
- [ ] En ambos casos (input o mapa), al confirmar, enviar los datos (lat/lng) al endpoint `getServiceabilityDetails` y avanzar al paso 2.

### Paso 2: Lógica según coverage_status
- [ ] Implementar lógica condicional tras la respuesta de `getServiceabilityDetails`:
    - Si `coverage_status` es "Yes":
        - [ ] Mostrar la página "Is there Coverage? - Yes" según Figma.
        - [ ] Mostrar opciones de planes disponibles (paso 3).
    - Si `coverage_status` es "Maybe":
        - [ ] Mostrar la página "Is there Coverage? - Maybe" según Figma.
        - [ ] Mostrar mensaje de cobertura parcial o dudosa y opciones según diseño (registro de interés, contacto, etc.).
    - Si `coverage_status` es "Not Yet":
        - [ ] Mostrar la página "Is there Coverage? - Not Yet" según Figma.
        - [ ] Mostrar mensaje de cobertura futura y opciones de registro de interés.
    - Si `coverage_status` es "No":
        - [ ] Mostrar la página "Is there Coverage? - No" según Figma.
        - [ ] Mostrar mensaje de no cobertura y opciones según diseño (registro de interés, contacto, etc.).

- [ ] Refactorizar navegación a páginas/vistas separadas
- [ ] Implementar sliders/carouseles para servicios y addons
- [ ] Adaptar integración API a los nuevos endpoints y flujo (`getServiceabilityDetails` → `getPackages`)
- [ ] Implementar modal para formulario de usuario y addons
- [ ] Mejorar logging y UI de depuración
- [ ] Actualizar documentación técnica y de usuario
- [ ] Pruebas de integración y compatibilidad
- [ ] Validar seguridad y sanitización de datos
- [ ] Revisar y actualizar el roadmap

### Paso 3: Selección de planes y addons (cuando hay cobertura)
- [ ] Mostrar los planes disponibles usando el diseño de la página "Choose Product" en Figma.
- [ ] Permitir al usuario seleccionar un plan/paquete.
- [ ] Al seleccionar un plan, mostrar un modal con:
    - [ ] Formulario de usuario (datos requeridos para la oportunidad).
    - [ ] Addons disponibles presentados en slider/carousel, siguiendo el diseño de la página "Coverage - Callback" en Figma.
- [ ] Permitir seleccionar addons y completar el formulario antes de avanzar al resumen/confirmación.

## Backlog y mejoras futuras

- [ ] Exportar/importar configuración del plugin
- [ ] Añadir tests automáticos (PHP y JS)
- [ ] Mejorar accesibilidad (a11y)
- [ ] Soporte multilenguaje (i18n)
- [ ] Integración con sistemas de analítica
- [ ] Mejorar la gestión de errores y mensajes al usuario
- [ ] Optimización de rendimiento frontend

## Notas de planificación

- Priorizar la modularidad y la mantenibilidad del código.
- Mantener toda la documentación y ejemplos de payloads actualizados en el README.
- Revisar dependencias entre tareas antes de iniciar cada sprint.
- Asignar responsables si el equipo crece.
- Revisar el backlog tras cada release para re-priorizar.

---

Este archivo debe mantenerse actualizado por el equipo de desarrollo. Si se completan tareas, marcar como hecho `[x]`. Si surgen nuevas necesidades, añadirlas al backlog o checklist correspondiente. 