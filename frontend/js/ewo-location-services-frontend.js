/**
 * Scripts para la parte pública del plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

(function ($) {
  "use strict";

  // Variables globales
  let map = null;
  let marker = null;
  let selectedService = null;
  let selectedAddons = [];
  let lastApiResponse = null; // Variable para almacenar la última respuesta de la API

  // Inicialización cuando el DOM está listo
  $(document).ready(function () {
    // Inicializar el mapa si el contenedor existe
    if ($("#ewo-map-container").length) {
      initMap();
    }

    // Eventos para los botones de localización
    $("#ewo-use-my-location").on("click", getUserLocation);
    // Manejar geocodificación al presionar Enter en el campo de dirección
    $("#ewo-address-input").on("keypress", function (e) {
      if (e.which === 13) {
        e.preventDefault();
        geocodeAddress();
      }
    });
    $("#ewo-search-services").on("click", handleSearchClick);

    // Eventos para navegación entre secciones
    $("#ewo-back-to-location").on("click", showLocationSection);
    $("#ewo-back-to-services").on("click", showServicesSection);
    $("#ewo-back-to-addons").on("click", showAddonsSection);
    $("#ewo-continue-to-user").on("click", showUserSection);

    // Manejar selección de servicio (evento delegado)
    $(document).on("click", ".ewo-select-service", selectService);

    // Manejar envío del formulario de usuario
    $("#ewo-user-form").on("submit", submitUserForm);

    // Eventos para el modal de depuración
    $("#ewo-debug-trigger").on("click", openDebugModal);
    $(".ewo-modal-close, #ewo-debug-close").on("click", closeDebugModal);
    $("#ewo-debug-copy").on("click", copyDebugData);

    // Cerrar el modal al hacer clic fuera del contenido
    $(window).on("click", function (event) {
      if ($(event.target).is("#ewo-debug-modal")) {
        closeDebugModal();
      }
    });
  });

  /**
   * Inicializa el mapa de OpenStreetMap con Leaflet
   */
  function initMap() {
    // Coordenadas iniciales (centro del mapa)
    const initialLat = 40.7128;
    const initialLng = -74.006;

    // Inicializar el mapa
    map = L.map("ewo-map-container").setView([initialLat, initialLng], 13);

    // Añadir capa de tiles (mapa base)
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    // Crear marcador inicial
    marker = L.marker([initialLat, initialLng], {
      draggable: true, // Permite arrastrar el marcador
    }).addTo(map);

    // Actualizar coordenadas cuando el marcador es arrastrado
    marker.on("dragend", function (event) {
      const position = marker.getLatLng();
      updateCoordinates(position.lat, position.lng);
    });

    // Actualizar marcador cuando se hace clic en el mapa
    map.on("click", function (event) {
      marker.setLatLng(event.latlng);
      updateCoordinates(event.latlng.lat, event.latlng.lng);

      // Obtener la dirección para esa ubicación
      reverseGeocode(event.latlng.lat, event.latlng.lng);

      // Animar el botón de búsqueda para llamar la atención
      $("#ewo-search-services").addClass("ewo-button-highlighted");
      setTimeout(function () {
        $("#ewo-search-services").removeClass("ewo-button-highlighted");
      }, 1500);
    });

    // Invalidar tamaño del mapa después de renderizar (para casos donde el mapa se muestra después de estar oculto)
    setTimeout(function () {
      map.invalidateSize();
    }, 100);
  }

  /**
   * Actualiza los campos ocultos con las coordenadas actuales
   */
  function updateCoordinates(lat, lng) {
    $("#ewo-latitude").val(lat);
    $("#ewo-longitude").val(lng);

    console.log(`Coordenadas actualizadas: lat=${lat}, lng=${lng}`); // Para depuración
  }

  /**
   * Obtiene la ubicación del usuario mediante la API de Geolocalización del navegador
   */
  function getUserLocation(e) {
    e.preventDefault();

    if (navigator.geolocation) {
      // Mostrar indicador de carga
      $("#ewo-use-my-location").prop("disabled", true);
      $("#ewo-use-my-location").html(
        '<span class="dashicons dashicons-location"></span> ' + "Localizando..."
      );

      navigator.geolocation.getCurrentPosition(
        // Éxito
        function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          // Actualizar mapa y marcador
          map.setView([lat, lng], 15);
          marker.setLatLng([lat, lng]);

          // Actualizar campos ocultos
          updateCoordinates(lat, lng);

          // Obtener la dirección de las coordenadas (geocodificación inversa)
          reverseGeocode(lat, lng);

          // Restaurar botón y destacar el botón de búsqueda para indicar que está listo para buscar
          $("#ewo-use-my-location").prop("disabled", false);
          $("#ewo-use-my-location").html(
            '<span class="dashicons dashicons-location"></span> ' +
              "Usar mi ubicación"
          );

          // Animar el botón de búsqueda para llamar la atención
          $("#ewo-search-services").addClass("ewo-button-highlighted");
          setTimeout(function () {
            $("#ewo-search-services").removeClass("ewo-button-highlighted");
          }, 1500);
        },
        // Error
        function (error) {
          console.error("Error al obtener la ubicación", error);

          let errorMessage = "Error al obtener tu ubicación.";
          switch (error.code) {
            case error.PERMISSION_DENIED:
              errorMessage = "Permiso de geolocalización denegado.";
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage = "Información de ubicación no disponible.";
              break;
            case error.TIMEOUT:
              errorMessage = "La solicitud de ubicación expiró.";
              break;
          }

          // Mostrar error
          alert(errorMessage);

          // Restaurar botón
          $("#ewo-use-my-location").prop("disabled", false);
          $("#ewo-use-my-location").html(
            '<span class="dashicons dashicons-location"></span> ' +
              "Usar mi ubicación"
          );
        },
        // Opciones
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0,
        }
      );
    } else {
      alert("Tu navegador no soporta geolocalización.");
    }
  }

  /**
   * Convierte dirección en coordenadas mediante la API de Nominatim (OpenStreetMap)
   * @param {Function} callback - Función opcional a llamar después de geocodificar exitosamente
   */
  function geocodeAddress(callback) {
    const address = $("#ewo-address-input").val().trim();

    if (!address) {
      return;
    }

    // Mostrar indicador de carga
    $("#ewo-address-input").prop("disabled", true);
    $("#ewo-search-services").prop("disabled", true);

    // Usar Nominatim para geocodificación
    $.ajax({
      url: "https://nominatim.openstreetmap.org/search",
      type: "GET",
      data: {
        q: address,
        format: "json",
        limit: 1,
      },
      success: function (data) {
        if (data && data.length > 0) {
          const result = data[0];
          const lat = parseFloat(result.lat);
          const lng = parseFloat(result.lon);

          // Actualizar mapa y marcador
          map.setView([lat, lng], 15);
          marker.setLatLng([lat, lng]);

          // Actualizar campos ocultos
          updateCoordinates(lat, lng);

          // Si hay una función de callback, ejecutarla
          if (typeof callback === "function") {
            callback(lat, lng);
          }
        } else {
          alert(
            "No se encontró la dirección. Por favor, intenta con otra dirección o usa el mapa para seleccionar tu ubicación."
          );
        }
      },
      error: function () {
        alert("Error al buscar la dirección. Por favor, intenta de nuevo.");
      },
      complete: function () {
        // Restaurar campos
        $("#ewo-address-input").prop("disabled", false);
        $("#ewo-search-services").prop("disabled", false);
      },
    });
  }

  /**
   * Realiza geocodificación inversa para obtener la dirección de unas coordenadas
   */
  function reverseGeocode(lat, lng) {
    $.ajax({
      url: "https://nominatim.openstreetmap.org/reverse",
      type: "GET",
      data: {
        lat: lat,
        lon: lng,
        format: "json",
      },
      success: function (data) {
        if (data && data.display_name) {
          $("#ewo-address-input").val(data.display_name);
        }
      },
      error: function () {
        console.error("Error en geocodificación inversa");
      },
    });
  }

  /**
   * Busca servicios basados en la ubicación seleccionada
   */
  function searchServices(e) {
    if (e) e.preventDefault();

    const lat = $("#ewo-latitude").val().trim();
    const lng = $("#ewo-longitude").val().trim();

    if (!lat || !lng) {
      alert(
        "Por favor, selecciona primero una ubicación en el mapa o ingresa una dirección."
      );
      return;
    }

    console.log(`Buscando servicios en: lat=${lat}, lng=${lng}`); // Para depuración

    // Mostrar sección de servicios y ocultar las demás
    $(".ewo-section").removeClass("active");
    $("#ewo-services-container").addClass("active");

    // Mostrar indicador de carga y ocultar errores
    $("#ewo-loading-services").show();
    $("#ewo-services-error").hide();
    $("#ewo-services-list").empty();

    // Realizar la solicitud AJAX al servidor
    $.ajax({
      url: ewoLocationServices.ajax_url,
      type: "POST",
      data: {
        action: "ewo_location_search",
        nonce: ewoLocationServices.nonce,
        latitude: lat,
        longitude: lng,
      },
      success: function (response) {
        console.log("Respuesta API recibida:", response); // Debug

        // Guardar la respuesta para la depuración
        lastApiResponse = response;

        if (response.success && response.data) {
          displayServices(response.data);
        } else {
          // Mostrar error
          const errorMessage =
            response.data && response.data.message
              ? response.data.message
              : "No se pudieron obtener los servicios. Por favor, intenta de nuevo.";

          showError("#ewo-services-error", errorMessage);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error en solicitud AJAX:", textStatus, errorThrown);

        // Guardar también los errores para depuración
        lastApiResponse = {
          error: true,
          status: textStatus,
          message: errorThrown,
          jqXHR: {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            responseText: jqXHR.responseText,
          },
        };

        showError(
          "#ewo-services-error",
          "Error al conectar con el servidor. Por favor, verifica tu conexión a internet e intenta de nuevo."
        );
      },
      complete: function () {
        $("#ewo-loading-services").hide();
      },
    });
  }

  /**
   * Muestra los servicios recibidos desde la API
   */
  function displayServices(data) {
    console.log("Datos recibidos:", data); // Debug

    // Manejar el nuevo formato de respuesta con datos de depuración
    let services = [];

    if (Array.isArray(data)) {
      // Si directamente es un array de servicios
      services = data;
    } else if (data.services && Array.isArray(data.services)) {
      // Si viene en el formato con propiedad 'services'
      services = data.services;
    } else if (
      data.serviceableLocations &&
      Array.isArray(data.serviceableLocations)
    ) {
      // Si viene en el formato de 'serviceableLocations' (desde el endpoint getServiceableLocationsByLatLng)
      services = data.serviceableLocations;
    } else if (
      data.raw_response &&
      data.raw_response.serviceableLocations &&
      Array.isArray(data.raw_response.serviceableLocations)
    ) {
      // Si viene encapsulado dentro de 'raw_response'
      services = data.raw_response.serviceableLocations;
    } else if (typeof data === "object" && data !== null) {
      // Inspeccionar la estructura del objeto para encontrar arrays que puedan contener servicios
      console.log("Buscando recursivamente servicios en:", Object.keys(data));

      // Buscar en el primer nivel
      for (const key in data) {
        if (Array.isArray(data[key]) && data[key].length > 0) {
          console.log(
            `Encontrado array en propiedad ${key}, verificando contenido...`
          );
          // Verificar si parece un array de servicios (tiene propiedades típicas)
          if (
            data[key][0] &&
            (data[key][0].name || data[key][0].id || data[key][0].price)
          ) {
            console.log(`El array en ${key} parece contener servicios válidos`);
            services = data[key];
            break;
          }
        } else if (typeof data[key] === "object" && data[key] !== null) {
          // Buscar en el segundo nivel
          for (const subKey in data[key]) {
            if (
              Array.isArray(data[key][subKey]) &&
              data[key][subKey].length > 0
            ) {
              console.log(`Verificando array en ${key}.${subKey}...`);
              if (
                data[key][subKey][0] &&
                (data[key][subKey][0].name ||
                  data[key][subKey][0].id ||
                  data[key][subKey][0].price)
              ) {
                console.log(
                  `El array en ${key}.${subKey} contiene servicios válidos`
                );
                services = data[key][subKey];
                break;
              }
            }
          }
          // Si ya encontramos servicios, salir del bucle principal
          if (services.length > 0) break;
        }
      }

      // Si no se encontraron servicios en la búsqueda recursiva, intentar con propiedades comunes
      if (services.length === 0) {
        services = data.services || data.locations || data.items || [];
      }
    } else {
      // Fallback para otros casos
      services = [];
    }

    // Asegurarse de que services sea un array antes de continuar
    if (!Array.isArray(services)) {
      console.error("Formato de servicios no válido:", services);
      services = [];
    }

    if (services.length === 0) {
      showError(
        "#ewo-services-error",
        "No hay servicios disponibles en tu ubicación en este momento."
      );
      return;
    }

    const $servicesList = $("#ewo-services-list");
    const $template = $("#ewo-service-template");

    services.forEach(function (service) {
      // Clonar la plantilla
      const $serviceItem = $($template.html());

      // Llenar con los datos del servicio
      $serviceItem
        .find(".ewo-service-name")
        .text(service.name || "Servicio sin nombre");
      $serviceItem.find(".ewo-service-price").text(formatPrice(service.price));
      $serviceItem
        .find(".ewo-service-description")
        .text(service.description || "Sin descripción");
      $serviceItem
        .find(".ewo-select-service")
        .attr("data-service-id", service.id);

      // Opcional: añadir más datos como atributos para usarlos después
      $serviceItem.attr("data-service-json", JSON.stringify(service));

      // Añadir al listado
      $servicesList.append($serviceItem);
    });
  }

  /**
   * Maneja la selección de un servicio y muestra las opciones de cross-sell
   */
  function selectService() {
    // Obtener el ID del servicio y otros datos
    const serviceId = $(this).data("service-id");
    const $serviceItem = $(this).closest(".ewo-service-item");
    const serviceDataStr = $serviceItem.attr("data-service-json");

    try {
      // Guardar los datos del servicio seleccionado
      selectedService = JSON.parse(serviceDataStr);

      // Guardar el ID en el campo oculto del formulario de usuario
      $("#ewo-selected-service").val(serviceId);

      // Mostrar addons si existen, o ir directamente al formulario de usuario
      if (selectedService.addons && selectedService.addons.length > 0) {
        displayAddons(selectedService.addons);
        showAddonsSection();
      } else {
        showUserSection();
      }
    } catch (e) {
      console.error("Error al procesar los datos del servicio", e);
      showError(
        "#ewo-services-error",
        "Error al seleccionar el servicio. Por favor, intenta de nuevo."
      );
    }
  }

  /**
   * Muestra los addons/opciones de cross-sell
   */
  function displayAddons(addons) {
    const $addonsList = $("#ewo-addons-list");
    const $template = $("#ewo-addon-template");

    // Limpiar lista anterior
    $addonsList.empty();

    addons.forEach(function (addon) {
      // Clonar la plantilla
      const $addonItem = $($template.html());

      // Llenar con los datos del addon
      $addonItem.find(".ewo-addon-name").text(addon.name || "Addon sin nombre");
      $addonItem.find(".ewo-addon-price").text(formatPrice(addon.price));
      $addonItem
        .find(".ewo-addon-description")
        .text(addon.description || "Sin descripción");
      $addonItem.find(".ewo-addon-select").val(addon.id);

      // Añadir al listado
      $addonsList.append($addonItem);
    });
  }

  /**
   * Maneja el envío del formulario de usuario
   */
  function submitUserForm(e) {
    e.preventDefault();

    // Validar contraseñas
    const password = $("#ewo-user-password").val();
    const passwordConfirm = $("#ewo-user-password-confirm").val();

    if (password !== passwordConfirm) {
      showError("#ewo-user-error", "Las contraseñas no coinciden.");
      return;
    }

    // Recopilar addons seleccionados
    selectedAddons = [];
    $(".ewo-addon-select:checked").each(function () {
      selectedAddons.push($(this).val());
    });

    // Deshabilitar el botón para evitar envíos duplicados
    const $submitButton = $("#ewo-submit-user");
    const originalButtonText = $submitButton.text();
    $submitButton.prop("disabled", true);
    $submitButton.text("Procesando...");

    // Ocultar mensajes de error previos
    $("#ewo-user-error").hide();

    // Preparar datos del formulario
    const formData = {
      action: "ewo_submit_user",
      nonce: ewoLocationServices.nonce,
      username: $("#ewo-user-username").val(),
      email: $("#ewo-user-email").val(),
      password: password,
      first_name: $("#ewo-user-first-name").val(),
      last_name: $("#ewo-user-last-name").val(),
      service_id: $("#ewo-selected-service").val(),
      addons: selectedAddons,
    };

    // Enviar solicitud AJAX
    $.ajax({
      url: ewoLocationServices.ajax_url,
      type: "POST",
      data: formData,
      success: function (response) {
        // Guardar la respuesta para la depuración
        lastApiResponse = response;

        if (response.success) {
          // Mostrar mensaje de éxito
          showConfirmationSection();

          // Redirigir después de un tiempo si se proporciona una URL
          if (response.data && response.data.redirect) {
            setTimeout(function () {
              window.location.href = response.data.redirect;
            }, 3000);
          }
        } else {
          // Mostrar error
          const errorMessage =
            response.data && response.data.message
              ? response.data.message
              : "Error al procesar tu registro. Por favor, intenta de nuevo.";

          showError("#ewo-user-error", errorMessage);
        }
      },
      error: function () {
        showError(
          "#ewo-user-error",
          "Error al conectar con el servidor. Por favor, verifica tu conexión a internet e intenta de nuevo."
        );
      },
      complete: function () {
        // Restaurar botón
        $submitButton.prop("disabled", false);
        $submitButton.text(originalButtonText);
      },
    });
  }

  /**
   * Funciones auxiliares para mostrar/ocultar secciones
   */
  function showLocationSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-location-form-container").addClass("active");
    // Invalidar tamaño del mapa al mostrarlo
    if (map) {
      setTimeout(function () {
        map.invalidateSize();
      }, 10);
    }
  }

  function showServicesSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-services-container").addClass("active");
  }

  function showAddonsSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-addons-container").addClass("active");
  }

  function showUserSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-user-container").addClass("active");
  }

  function showConfirmationSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-confirmation-container").addClass("active");
  }

  /**
   * Muestra un mensaje de error
   */
  function showError(selector, message) {
    const $errorContainer = $(selector);
    $errorContainer.find(".ewo-error-message").text(message);
    $errorContainer.show();
  }

  /**
   * Formatea un precio para mostrar
   */
  function formatPrice(price) {
    if (price === undefined || price === null) {
      return "";
    }

    // Convertir a número si es string
    const numPrice = typeof price === "string" ? parseFloat(price) : price;

    // Formatear con dos decimales y símbolo de moneda
    return "$" + numPrice.toFixed(2);
  }

  /**
   * Funciones para el modal de depuración
   */

  /**
   * Abre el modal de depuración y muestra la última respuesta JSON de la API
   */
  function openDebugModal() {
    console.log("Abriendo modal de depuración", lastApiResponse); // Debug

    if (!lastApiResponse) {
      alert(
        "No hay datos de API disponibles para mostrar. Realiza una búsqueda primero."
      );
      return;
    }

    try {
      // Prepara las distintas vistas
      prepareDebugViews(lastApiResponse);

      // Activa la pestaña de vista formateada por defecto
      $(".ewo-debug-tab-button[data-tab='formatted']").addClass("active");
      $("#ewo-debug-tab-formatted").addClass("active");

      // Mostrar el modal
      $("#ewo-debug-modal").fadeIn(300);

      // Configurar eventos de las pestañas
      $(".ewo-debug-tab-button")
        .off("click")
        .on("click", function () {
          const tab = $(this).data("tab");

          // Cambiar clases activas
          $(".ewo-debug-tab-button").removeClass("active");
          $(this).addClass("active");

          $(".ewo-debug-tab").removeClass("active");
          $("#ewo-debug-tab-" + tab).addClass("active");
        });
    } catch (error) {
      console.error("Error al mostrar datos de API:", error);
      alert("Error al procesar los datos de la API para mostrar en el modal.");
    }
  }

  /**
   * Prepara las diferentes vistas para el modal de depuración
   */
  function prepareDebugViews(apiResponse) {
    // Vista JSON sin formato
    const jsonString = JSON.stringify(apiResponse, null, 2);
    $("#ewo-debug-json").text(jsonString);

    // Vista formateada para servicios
    const $formattedContainer = $("#ewo-debug-formatted-services");
    $formattedContainer.empty();

    // Intentar extraer los servicios o ubicaciones
    let services = [];

    if (apiResponse.data && apiResponse.data.services) {
      services = apiResponse.data.services;
    } else if (
      apiResponse.data &&
      apiResponse.data.raw_response &&
      apiResponse.data.raw_response.serviceableLocations
    ) {
      services = apiResponse.data.raw_response.serviceableLocations;
    }

    if (services.length > 0) {
      // Crear tabla para visualizar los servicios
      const $table = $(
        '<table class="ewo-debug-table"><thead><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Descripción</th></tr></thead><tbody></tbody></table>'
      );

      services.forEach(function (service) {
        const $row = $("<tr></tr>");
        $row.append("<td>" + (service.id || "N/A") + "</td>");
        $row.append("<td>" + (service.name || "Sin nombre") + "</td>");
        $row.append("<td>" + formatPrice(service.price) + "</td>");
        $row.append(
          "<td>" + (service.description || "Sin descripción") + "</td>"
        );

        $table.find("tbody").append($row);
      });

      $formattedContainer.append($table);
    } else {
      $formattedContainer.html(
        "<p>No se encontraron servicios o ubicaciones en la respuesta API.</p>"
      );
    }

    // Vista de análisis de estructura
    const $structureContainer = $("#ewo-debug-structure");
    $structureContainer.empty();

    if (
      apiResponse.data &&
      apiResponse.data.debug &&
      apiResponse.data.debug.response_structure
    ) {
      const structure = apiResponse.data.debug.response_structure;

      const $structureInfo = $('<div class="ewo-structure-info"></div>');
      $structureInfo.append(
        "<p><strong>Tipo de respuesta:</strong> " + structure.type + "</p>"
      );

      if (structure.keys && structure.keys.length > 0) {
        $structureInfo.append(
          "<p><strong>Claves:</strong> " + structure.keys.join(", ") + "</p>"
        );
      }

      if (structure.has_array) {
        $structureInfo.append(
          "<p><strong>Contiene array:</strong> Sí (longitud: " +
            structure.array_length +
            ")</p>"
        );
        $structureInfo.append(
          "<p><strong>Posible array de servicios:</strong> " +
            (structure.potential_services ? "Sí" : "No") +
            "</p>"
        );
      }

      $structureContainer.append($structureInfo);

      // Si hay una estructura anidada, mostrarla
      if (structure.children) {
        const $childrenTitle = $("<h4>Propiedades anidadas:</h4>");
        $structureContainer.append($childrenTitle);

        for (const key in structure.children) {
          const child = structure.children[key];

          const $childInfo = $('<div class="ewo-structure-child"></div>');
          $childInfo.append("<h5>" + key + "</h5>");
          $childInfo.append("<p><strong>Tipo:</strong> " + child.type + "</p>");

          if (child.has_array) {
            $childInfo.append(
              "<p><strong>Contiene array:</strong> Sí (longitud: " +
                child.array_length +
                ")</p>"
            );
            $childInfo.append(
              "<p><strong>Posible array de servicios:</strong> " +
                (child.potential_services ? "Sí" : "No") +
                "</p>"
            );
          }

          $structureContainer.append($childInfo);
        }
      }
    } else {
      $structureContainer.html(
        "<p>No hay información de estructura disponible.</p>"
      );
    }
  }

  /**
   * Cierra el modal de depuración
   */
  function closeDebugModal() {
    $("#ewo-debug-modal").fadeOut(200);
  }

  /**
   * Copia los datos JSON al portapapeles
   */
  function copyDebugData() {
    // Determinar qué contenido copiar según la pestaña activa
    let content = "";

    if ($("#ewo-debug-tab-raw").hasClass("active")) {
      content = $("#ewo-debug-json").text();
    } else if ($("#ewo-debug-tab-formatted").hasClass("active")) {
      content = "SERVICIOS ENCONTRADOS:\n\n";

      $("#ewo-debug-formatted-services table tbody tr").each(function () {
        const $row = $(this);
        const id = $row.find("td:eq(0)").text();
        const name = $row.find("td:eq(1)").text();
        const price = $row.find("td:eq(2)").text();

        content += `${name} (ID: ${id}) - ${price}\n`;
      });
    } else {
      content = "ANÁLISIS DE ESTRUCTURA DE RESPUESTA API:\n\n";
      content += $("#ewo-debug-structure").text();
    }

    // Crear un elemento de texto temporal
    const tempTextArea = document.createElement("textarea");
    tempTextArea.value = content;
    document.body.appendChild(tempTextArea);

    // Seleccionar y copiar el texto
    tempTextArea.select();
    document.execCommand("copy");

    // Eliminar el elemento temporal
    document.body.removeChild(tempTextArea);

    // Mostrar confirmación
    const $button = $("#ewo-debug-copy");
    const originalText = $button.text();
    $button.text("¡Copiado!");

    // Restaurar el texto original después de un tiempo
    setTimeout(function () {
      $button.text(originalText);
    }, 2000);
  }

  /**
   * Maneja el clic en el botón de búsqueda de servicios
   * Verifica si hay coordenadas o si se necesita geocodificar la dirección primero
   */
  function handleSearchClick(e) {
    e.preventDefault();

    const lat = $("#ewo-latitude").val().trim();
    const lng = $("#ewo-longitude").val().trim();
    const address = $("#ewo-address-input").val().trim();

    // Si ya tenemos coordenadas, buscar servicios directamente
    if (lat && lng) {
      searchServices(e);
      return;
    }

    // Si hay una dirección pero no coordenadas, geocodificar primero
    if (address) {
      // Mostrar indicador de carga
      $("#ewo-search-services").prop("disabled", true);
      const originalButtonText = $("#ewo-search-services").text();
      $("#ewo-search-services").text("Buscando ubicación...");

      // Usar Nominatim para geocodificación
      $.ajax({
        url: "https://nominatim.openstreetmap.org/search",
        type: "GET",
        data: {
          q: address,
          format: "json",
          limit: 1,
        },
        success: function (data) {
          if (data && data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            // Actualizar mapa y marcador
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);

            // Actualizar campos ocultos
            updateCoordinates(lat, lng);

            // Buscar servicios con las coordenadas obtenidas
            searchServices(e);
          } else {
            alert(
              "No se encontró la dirección. Por favor, intenta con otra dirección o usa el mapa para seleccionar tu ubicación."
            );
          }
        },
        error: function () {
          alert("Error al buscar la dirección. Por favor, intenta de nuevo.");
        },
        complete: function () {
          // Restaurar botón
          $("#ewo-search-services").prop("disabled", false);
          $("#ewo-search-services").text(originalButtonText);
        },
      });
    } else {
      // No hay ni coordenadas ni dirección
      alert(
        "Por favor, ingresa una dirección o selecciona un punto en el mapa."
      );
    }
  }
})(jQuery);
