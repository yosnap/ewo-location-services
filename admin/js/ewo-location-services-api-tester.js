/**
 * JavaScript para la página del probador de API
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

(function ($) {
  "use strict";

  // Variables globales
  let map = null;
  let marker = null;

  // Inicialización cuando el DOM está listo
  $(document).ready(function () {
    // Inicializar eventos
    $("#ewo-use-map").on("click", toggleMapContainer);
    $("#ewo-api-test-form").on("submit", handleTestSubmit);
    $(".ewo-tab-button").on("click", switchTab);

    // Inicializar el mapa (oculto inicialmente)
    initMap();
  });

  /**
   * Inicializa el mapa de OpenStreetMap con Leaflet
   */
  function initMap() {
    // Coordenadas iniciales (las mismas que en los inputs)
    const initialLat = parseFloat($("#latitude").val()) || 40.7128;
    const initialLng = parseFloat($("#longitude").val()) || -74.006;

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
    });

    // Invalidar tamaño del mapa después de renderizar (necesario para mapas que se muestran tras estar ocultos)
    setTimeout(function () {
      map.invalidateSize();
    }, 100);
  }

  /**
   * Actualiza los campos de coordenadas con los valores proporcionados
   */
  function updateCoordinates(lat, lng) {
    $("#latitude").val(lat.toFixed(6));
    $("#longitude").val(lng.toFixed(6));
  }

  /**
   * Alterna la visibilidad del contenedor del mapa
   */
  function toggleMapContainer() {
    const $mapContainer = $("#ewo-map-container");

    if ($mapContainer.is(":visible")) {
      $mapContainer.slideUp(300);
      $("#ewo-use-map").text("Select on Map");
    } else {
      $mapContainer.slideDown(300, function () {
        // Actualizar el mapa al mostrar (para corregir problemas de renderizado)
        map.invalidateSize();

        // Centrar el mapa en las coordenadas actuales
        const lat = parseFloat($("#latitude").val()) || 40.7128;
        const lng = parseFloat($("#longitude").val()) || -74.006;
        map.setView([lat, lng], 13);
        marker.setLatLng([lat, lng]);
      });
      $("#ewo-use-map").text("Hide Map");
    }
  }

  /**
   * Maneja el envío del formulario de prueba de API
   */
  function handleTestSubmit(e) {
    e.preventDefault();

    // Mostrar spinner
    $("#ewo-test-spinner").addClass("is-active");

    // Ocultar contenedor de respuesta anterior
    $("#ewo-api-response-container").hide();

    // Recopilar datos del formulario
    const endpoint = $("#endpoint_type").val();
    const environment = $("#environment").val();
    const latitude = $("#latitude").val();
    const longitude = $("#longitude").val();

    // Validar datos
    if (!latitude || !longitude) {
      alert("Por favor, ingrese coordenadas válidas.");
      $("#ewo-test-spinner").removeClass("is-active");
      return;
    }

    // Enviar solicitud AJAX al servidor de WordPress
    $.ajax({
      url: ewoApiTester.ajax_url,
      type: "POST",
      data: {
        action: "ewo_api_test",
        nonce: ewoApiTester.nonce,
        endpoint_type: endpoint,
        environment: environment,
        latitude: latitude,
        longitude: longitude,
      },
      success: function (response) {
        displayResponse(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        displayError(
          "Error en la solicitud AJAX: " + textStatus + " - " + errorThrown
        );
      },
      complete: function () {
        // Ocultar spinner
        $("#ewo-test-spinner").removeClass("is-active");
      },
    });
  }

  /**
   * Muestra la respuesta de la API en el contenedor
   */
  function displayResponse(response) {
    // Mostrar el contenedor de respuesta
    $("#ewo-api-response-container").show();

    // Determinar si la solicitud fue exitosa
    const success = response.success === true;

    // Actualizar el ícono y texto de estado
    $("#ewo-response-status-icon").html(success ? "✅" : "❌");
    $("#ewo-response-status-text").text(
      response.data ? response.data.message : "Error desconocido"
    );

    // Limpiar los contenedores de contenido
    $("#ewo-response-formatted").empty();
    $("#ewo-response-json").empty();
    $("#ewo-debug-json").empty();

    if (success) {
      // Mostrar datos formateados
      formatResponseData(response.data.raw_response);

      // Mostrar JSON en bruto
      const jsonString = JSON.stringify(response.data.raw_response, null, 2);
      $("#ewo-response-json").text(jsonString);

      // Mostrar información de depuración
      const debugString = JSON.stringify(response.data.debug, null, 2);
      $("#ewo-debug-json").text(debugString);
    } else {
      // Mostrar mensaje de error en todas las vistas
      $("#ewo-response-formatted").html(
        '<div class="ewo-error-message">' +
          (response.data ? response.data.message : "Error desconocido") +
          "</div>"
      );

      // Mostrar datos en bruto si están disponibles
      if (response.data && response.data.raw_response) {
        const rawString =
          typeof response.data.raw_response === "string"
            ? response.data.raw_response
            : JSON.stringify(response.data.raw_response, null, 2);
        $("#ewo-response-json").text(rawString);
      } else {
        $("#ewo-response-json").text("No hay datos disponibles");
      }

      // Mostrar información de depuración
      if (response.data && response.data.debug) {
        const debugString = JSON.stringify(response.data.debug, null, 2);
        $("#ewo-debug-json").text(debugString);
      } else {
        $("#ewo-debug-json").text(
          "No hay información de depuración disponible"
        );
      }
    }
  }

  /**
   * Muestra un mensaje de error genérico
   */
  function displayError(message) {
    // Mostrar el contenedor de respuesta
    $("#ewo-api-response-container").show();

    // Actualizar el ícono y texto de estado
    $("#ewo-response-status-icon").html("❌");
    $("#ewo-response-status-text").text(message);

    // Limpiar los contenedores de contenido
    $("#ewo-response-formatted").html(
      '<div class="ewo-error-message">' + message + "</div>"
    );
    $("#ewo-response-json").text("No se recibió respuesta del servidor");
    $("#ewo-debug-json").text("No hay información de depuración disponible");
  }

  /**
   * Formatea la respuesta de la API para mostrarla de manera amigable
   */
  function formatResponseData(data) {
    const $container = $("#ewo-response-formatted");

    // Si no hay datos, mostrar mensaje
    if (!data) {
      $container.html(
        '<div class="ewo-notice">No hay datos para mostrar</div>'
      );
      return;
    }

    // Si hay un campo serviceableLocations, es una respuesta del endpoint getServiceableLocationsByLatLng
    if (data.serviceableLocations && Array.isArray(data.serviceableLocations)) {
      formatServiceableLocations(data.serviceableLocations);
    }
    // Si hay un campo serviceabilityDetails, es una respuesta del endpoint getServiceabilityDetails
    else if (data.serviceabilityDetails) {
      formatServiceabilityDetails(data.serviceabilityDetails);
    }
    // En caso contrario, mostrar una vista genérica
    else {
      formatGenericResponse(data);
    }
  }

  /**
   * Formatea los datos de ubicaciones para mostrarlos
   */
  function formatServiceableLocations(locations) {
    const $container = $("#ewo-response-formatted");

    if (locations.length === 0) {
      $container.html(
        '<div class="ewo-notice">No se encontraron ubicaciones disponibles para las coordenadas especificadas.</div>'
      );
      return;
    }

    let html = '<div class="ewo-locations-list">';
    html += "<h4>Ubicaciones Disponibles (" + locations.length + ")</h4>";

    // Crear tabla para mostrar las ubicaciones
    html += '<table class="wp-list-table widefat fixed striped">';
    html += "<thead><tr>";
    html += "<th>ID</th>";
    html += "<th>Nombre</th>";
    html += "<th>Distancia</th>";
    html += "<th>Estado</th>";
    html += "</tr></thead>";
    html += "<tbody>";

    locations.forEach(function (location) {
      html += "<tr>";
      html += "<td>" + (location.id || "N/A") + "</td>";
      html += "<td>" + (location.name || "Sin nombre") + "</td>";
      html +=
        "<td>" +
        (location.distance ? location.distance + " km" : "N/A") +
        "</td>";
      html += "<td>" + (location.state || "N/A") + "</td>";
      html += "</tr>";
    });

    html += "</tbody></table>";
    html += "</div>";

    $container.html(html);
  }

  /**
   * Formatea los detalles de servicio para mostrarlos
   */
  function formatServiceabilityDetails(details) {
    const $container = $("#ewo-response-formatted");

    let html = '<div class="ewo-details-container">';
    html += "<h4>Detalles de Servicio</h4>";

    // Información básica
    html += '<div class="ewo-detail-section">';
    html += "<h5>Información Básica</h5>";
    html += '<table class="wp-list-table widefat fixed striped">';
    html += "<tbody>";
    html += "<tr><th>ID</th><td>" + (details.id || "N/A") + "</td></tr>";
    html +=
      "<tr><th>Nombre</th><td>" + (details.name || "Sin nombre") + "</td></tr>";
    html += "<tr><th>Estado</th><td>" + (details.state || "N/A") + "</td></tr>";
    html +=
      "<tr><th>Es Serviciable</th><td>" +
      (details.isServiceable ? "Sí" : "No") +
      "</td></tr>";
    html += "</tbody></table>";
    html += "</div>";

    // Servicios disponibles
    if (details.availableServices && details.availableServices.length > 0) {
      html += '<div class="ewo-detail-section">';
      html += "<h5>Servicios Disponibles</h5>";
      html += '<ul class="ewo-services-list">';
      details.availableServices.forEach(function (service) {
        html += "<li>" + service + "</li>";
      });
      html += "</ul>";
      html += "</div>";
    }

    html += "</div>";
    $container.html(html);
  }

  /**
   * Formatea una respuesta genérica para mostrarla
   */
  function formatGenericResponse(data) {
    const $container = $("#ewo-response-formatted");

    let html = '<div class="ewo-generic-response">';
    html += "<h4>Respuesta del API</h4>";

    // Convertir el objeto a una tabla genérica
    html += '<table class="wp-list-table widefat fixed striped">';
    html += "<thead><tr><th>Campo</th><th>Valor</th></tr></thead>";
    html += "<tbody>";

    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        const value = data[key];

        // Formatear el valor según su tipo
        let displayValue = "";
        if (value === null) {
          displayValue = "<em>null</em>";
        } else if (typeof value === "object") {
          displayValue = "<pre>" + JSON.stringify(value, null, 2) + "</pre>";
        } else if (typeof value === "boolean") {
          displayValue = value ? "Verdadero" : "Falso";
        } else {
          displayValue = value.toString();
        }

        html += "<tr>";
        html += "<th>" + key + "</th>";
        html += "<td>" + displayValue + "</td>";
        html += "</tr>";
      }
    }

    html += "</tbody></table>";
    html += "</div>";

    $container.html(html);
  }

  /**
   * Cambia entre las diferentes pestañas de visualización
   */
  function switchTab() {
    // Quitar la clase activa de todas las pestañas
    $(".ewo-tab-button").removeClass("active");
    $(".ewo-tab-content").removeClass("active");

    // Añadir la clase activa a la pestaña seleccionada
    $(this).addClass("active");

    // Mostrar el contenido de la pestaña seleccionada
    const tabId = $(this).data("tab");
    $("#ewo-response-" + tabId).addClass("active");
  }
})(jQuery);
