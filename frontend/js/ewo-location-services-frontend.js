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

  // --- CONFIGURABLE OPTIONS ---
  const DEFAULT_COLUMNS = (window.ewoServiceListingOptions && ewoServiceListingOptions.columns) ? ewoServiceListingOptions.columns : 3;
  const DEFAULT_PER_PAGE = (window.ewoServiceListingOptions && ewoServiceListingOptions.per_page) ? ewoServiceListingOptions.per_page : 8;
  const SHOW_PAGINATION = (window.ewoServiceListingOptions && ewoServiceListingOptions.show_pagination === 'yes');
  const LOAD_MORE = (window.ewoServiceListingOptions && ewoServiceListingOptions.load_more === 'yes');
  const DEFAULT_LISTING_MODE = (window.ewoServiceListingOptions && ewoServiceListingOptions.listing_mode) ? ewoServiceListingOptions.listing_mode : 'grid';
  const SHOW_FILTERS = (window.ewoServiceListingOptions && ewoServiceListingOptions.show_filters === 'yes');
  const CARD_COLOR_USAGE = (window.ewoServiceListingOptions && ewoServiceListingOptions.card_color_usage) ? ewoServiceListingOptions.card_color_usage : 'none';

  // --- STATE ---
  let columns = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.columns) ? parseInt(window.ewoServiceListingOptions.columns, 10) : 3;
  let perPage = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.per_page) ? parseInt(window.ewoServiceListingOptions.per_page, 10) : 8;
  let currentView = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.listing_mode) ? window.ewoServiceListingOptions.listing_mode : 'grid';
  let currentPage = 1;
  let servicesData = [];
  let filteredServices = [];
  let isLoading = false;
  let searchQuery = '';
  let networkTypeFilter = '';
  let ordering = 'default';

  // Inicialización cuando el DOM está listo
  $(document).ready(function () {
    // Cambiar estilo de pasos según config
    $('#ewo-form-steps').removeClass('ewo-form-steps-progress-bar ewo-form-steps-circles');
    if (window.ewoServiceListingOptions && window.ewoServiceListingOptions.form_steps_style === 'circles') {
      $('#ewo-form-steps').addClass('ewo-form-steps-circles');
    } else {
      $('#ewo-form-steps').addClass('ewo-form-steps-progress-bar');
    }
    // Marcar paso activo inicial
    setActiveStep(1);

    // Autocompletar datos si el usuario está logueado
    if (window.ewoUserData && window.ewoUserData.logged_in) {
      $('#ewo-user-first-name').val(window.ewoUserData.first_name || '').prop('readonly', true);
      $('#ewo-user-last-name').val(window.ewoUserData.last_name || '').prop('readonly', true);
      $('#ewo-user-email').val(window.ewoUserData.email || '').prop('readonly', true);
      $('#ewo-user-username').val(window.ewoUserData.username || '').prop('readonly', true);
      $('#ewo-user-password, #ewo-user-password-confirm').prop('required', false);
    }

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

    // Eventos para filtros, buscador, columnas, paginación, load more y cambio de vista
    $(document).on('input', '#ewo-service-search', function () {
      searchQuery = $(this).val();
      applyFiltersAndOrdering();
    });
    $(document).on('change', '#ewo-network-type-filter', function () {
      networkTypeFilter = $(this).val();
      applyFiltersAndOrdering();
    });
    $(document).on('change', '#ewo-service-ordering', function () {
      ordering = $(this).val();
      applyFiltersAndOrdering();
    });
    $(document).on('change', '#ewo-columns-select', function () {
      columns = parseInt($(this).val(), 10);
      renderServices();
    });
    // Evento para perPage
    $(document).on('change', '#ewo-per-page-select', function () {
      perPage = parseInt($(this).val(), 10);
      currentPage = 1;
      renderServices();
    });
    $(document).on('click', '.ewo-toggle-btn', function () {
      currentView = $(this).data('view');
      renderServices();
    });
    $(document).on('click', '.ewo-page-btn', function () {
      const page = parseInt($(this).data('page'), 10);
      if (!isNaN(page)) {
        currentPage = page;
        renderServices();
      }
    });
    $(document).on('click', '.ewo-load-more-btn', function () {
      currentPage++;
      renderServices();
    });
  });

  /**
   * Inicializa el mapa de OpenStreetMap con Leaflet
   */
  function initMap() {
    // Coordenadas iniciales (centro del mapa)
    const initialLat = 37.806479687628936;
    const initialLng = -89.07903653069094;

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
        '<span class="dashicons dashicons-location"></span> ' + "Locating..."
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
              "Use my location"
          );

          // Animar el botón de búsqueda para llamar la atención
          $("#ewo-search-services").addClass("ewo-button-highlighted");
          setTimeout(function () {
            $("#ewo-search-services").removeClass("ewo-button-highlighted");
          }, 1500);
        },
        // Error
        function (error) {
          console.error("Error getting your location", error);

          let errorMessage = "Error getting your location.";
          switch (error.code) {
            case error.PERMISSION_DENIED:
              errorMessage = "Geolocation permission denied.";
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage = "Location information unavailable.";
              break;
            case error.TIMEOUT:
              errorMessage = "Location request timed out.";
              break;
          }

          // Mostrar error
          alert(errorMessage);

          // Restaurar botón
          $("#ewo-use-my-location").prop("disabled", false);
          $("#ewo-use-my-location").html(
            '<span class="dashicons dashicons-location"></span> ' +
              "Use my location"
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
      alert("Your browser does not support geolocation.");
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
            "Address not found. Please try another address or use the map to select your location."
          );
        }
      },
      error: function () {
        alert("Error searching for the address. Please try again.");
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
        console.error("Error in reverse geocoding");
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
        "Please select a location on the map or enter an address first."
      );
      return;
    }

    console.log(`Buscando servicios en: lat=${lat}, lng=${lng}`); // Para depuración

    // Mostrar sección de servicios y ocultar las demás
    $(".ewo-section").removeClass("active");
    $("#ewo-available-services-section").addClass("active");

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
              : "Could not retrieve services. Please try again.";

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
          "Error connecting to the server. Please check your internet connection and try again."
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

    let services = [];

    if (Array.isArray(data)) {
      services = data;
    } else if (data.services && Array.isArray(data.services)) {
      services = data.services;
    } else if (data.serviceableLocations && Array.isArray(data.serviceableLocations)) {
      services = data.serviceableLocations;
    } else if (
      data.raw_response &&
      data.raw_response["return-data"] &&
      Array.isArray(data.raw_response["return-data"]["serviceability-info"])
    ) {
      services = data.raw_response["return-data"]["serviceability-info"];
    } else if (typeof data === "object" && data !== null) {
      // Buscar el primer array de objetos que tenga coverage_code y network_type
      for (const key in data) {
        if (Array.isArray(data[key]) && data[key].length > 0) {
          const first = data[key][0];
          if (first.coverage_code && first.network_type) {
            services = data[key];
            break;
          }
        }
      }
    } else {
      services = [];
    }

    if (!Array.isArray(services)) {
      console.error("Formato de servicios no válido:", services);
      services = [];
    }

    if (services.length === 0) {
      showError(
        "#ewo-services-error",
        "No services available in your location at this time."
      );
      return;
    }

    servicesData = services;
    filteredServices = services;
    currentPage = 1;
    applyFiltersAndOrdering();
    showServicesSection();
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
        "Error selecting the service. Please try again."
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
      $addonItem.find(".ewo-addon-name").text(addon.name || "Unnamed addon");
      $addonItem.find(".ewo-addon-price").text(formatPrice(addon.price));
      $addonItem
        .find(".ewo-addon-description")
        .text(addon.description || "No description");
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
      showError("#ewo-user-error", "Passwords do not match.");
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
    $submitButton.text("Processing...");

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
              : "Error processing your registration. Please try again.";

          showError("#ewo-user-error", errorMessage);
        }
      },
      error: function () {
        showError(
          "#ewo-user-error",
          "Error connecting to the server. Please check your internet connection and try again."
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
    setActiveStep(1);
    if (map) {
      setTimeout(function () {
        map.invalidateSize();
      }, 10);
    }
  }

  function showServicesSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-available-services-section").addClass("active");
    setActiveStep(2);
  }

  function showAddonsSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-addons-container").addClass("active");
    setActiveStep(3);
  }

  function showUserSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-user-container").addClass("active");
    setActiveStep(4);
  }

  function showConfirmationSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-confirmation-container").addClass("active");
    setActiveStep(5);
  }

  // Renderizar pasos personalizados
  function renderCustomSteps() {
    const $stepsOl = $('#ewo-form-steps');
    const $stepsProgress = $('#ewo-form-steps-progress');
    const opts = window.ewoServiceListingOptions || {};
    const size = opts.step_size ? parseInt(opts.step_size, 10) : 32;
    const showLabels = opts.show_step_labels === 'yes';
    const iconType = opts.step_icon_type || 'dashicons';
    const style = opts.form_steps_style || 'progress_bar';
    const activeColor = opts.step_active_color || '#c2185b';
    const inactiveColor = opts.step_inactive_color || '#e5e1e1';
    const totalSteps = $stepsOl.find('.ewo-step').length;
    let activeStep = window.ewoCurrentStep || 1;

    if (style === 'circles') {
      $stepsOl.show();
      $stepsProgress.hide();
      // ... render de círculos en <ol> como antes ...
    } else if (style === 'progress_bar') {
      $stepsOl.hide();
      $stepsProgress.show();
      // Renderizar barra de progreso
      let iconsHtml = '<div class="ewo-progress-icons" style="display:flex;justify-content:space-between;margin-bottom:6px;">';
      for (let i = 1; i <= totalSteps; i++) {
        let iconHtml = '';
        let iconClass = (i === activeStep) ? 'active' : '';
        if (iconType === 'dashicons') {
          const icon = opts['step_icon_' + i] || 'location';
          iconHtml = '<span class="dashicons dashicons-' + icon + ' ' + iconClass + '" aria-hidden="true" style="font-size:' + (size * 0.7) + 'px;line-height:' + size + 'px;display:inline-block;"></span>';
        } else if (iconType === 'svg') {
          const svgUrl = opts['step_svg_' + i] || '';
          if (svgUrl) {
            iconHtml = '<img src="' + svgUrl + '" class="' + iconClass + '" style="width:' + (size * 0.7) + 'px;height:' + (size * 0.7) + 'px;display:block;margin:auto;" alt="Step icon">';
          } else {
            const icon = opts['step_icon_' + i] || 'location';
            iconHtml = '<span class="dashicons dashicons-' + icon + ' ' + iconClass + '" aria-hidden="true" style="font-size:' + (size * 0.7) + 'px;line-height:' + size + 'px;display:inline-block;"></span>';
          }
        } else if (iconType === 'none') {
          iconHtml = '<span class="ewo-step-number ' + iconClass + '" style="font-size:' + (size * 0.7) + 'px;line-height:' + size + 'px;">' + i + '</span>';
        }
        iconsHtml += '<span class="ewo-progress-icon ' + iconClass + '" style="flex:1;text-align:center;">' + iconHtml + '</span>';
      }
      iconsHtml += '</div>';
      // Labels
      let labelsHtml = '<div class="ewo-progress-labels" style="display:flex;justify-content:space-between;margin-bottom:6px;">';
      for (let i = 1; i <= totalSteps; i++) {
        const label = opts['step_label_' + i] || $stepsOl.find('.ewo-step').eq(i-1).text().replace(/^\d+\.?\s*/, '');
        const activeClass = (i === activeStep) ? 'active' : '';
        labelsHtml += '<span class="ewo-progress-label ' + activeClass + '" style="flex:1;text-align:center;font-size:0.98em;padding:0 4px;min-width:60px;white-space:nowrap;">' + label + '</span>';
      }
      labelsHtml += '</div>';
      // Barra de fondo y relleno
      let barHtml = '<div class="ewo-progress-bar-bg" style="position:relative;height:10px;border-radius:5px;background:var(--ewo-inactive-bg,#eee);overflow:hidden;">';
      const percent = (activeStep/totalSteps)*100;
      barHtml += '<div class="ewo-progress-bar-fill" style="position:absolute;left:0;top:0;height:100%;width:' + percent + '%;background:var(--ewo-active-color,#c2185b);transition:width 0.5s cubic-bezier(.4,0,.2,1);"></div>';
      barHtml += '</div>';
      // Renderizar en el div
      $stepsProgress.html(iconsHtml + barHtml + labelsHtml);
    }
    $('#ewo-form-steps, #ewo-form-steps-progress').addClass('ewo-steps-ready');
  }

  // Llama a renderCustomSteps al cargar y cada vez que cambie el paso
  renderCustomSteps();

  function setActiveStep(step) {
    window.ewoCurrentStep = step;
    $('#ewo-form-steps .ewo-step').removeClass('active');
    $('#ewo-form-steps .ewo-step[data-step="' + step + '"]').addClass('active');
    renderCustomSteps();
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
        "No API data available to display. Please perform a search first."
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
      alert("Error processing API data for display in the modal.");
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
        '<table class="ewo-debug-table"><thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Description</th></tr></thead><tbody></tbody></table>'
      );

      services.forEach(function (service) {
        const $row = $("<tr></tr>");
        $row.append("<td>" + (service.id || "N/A") + "</td>");
        $row.append("<td>" + (service.name || "No name") + "</td>");
        $row.append("<td>" + formatPrice(service.price) + "</td>");
        $row.append(
          "<td>" + (service.description || "No description") + "</td>"
        );

        $table.find("tbody").append($row);
      });

      $formattedContainer.append($table);
    } else {
      $formattedContainer.html(
        "<p>No services or locations found in the API response.</p>"
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
        "<p><strong>Response type:</strong> " + structure.type + "</p>"
      );

      if (structure.keys && structure.keys.length > 0) {
        $structureInfo.append(
          "<p><strong>Keys:</strong> " + structure.keys.join(", ") + "</p>"
        );
      }

      if (structure.has_array) {
        $structureInfo.append(
          "<p><strong>Contains array:</strong> Yes (length: " +
            structure.array_length +
            ")</p>"
        );
        $structureInfo.append(
          "<p><strong>Potential service array:</strong> " +
            (structure.potential_services ? "Yes" : "No") +
            "</p>"
        );
      }

      $structureContainer.append($structureInfo);

      // Si hay una estructura anidada, mostrarla
      if (structure.children) {
        const $childrenTitle = $("<h4>Nested properties:</h4>");
        $structureContainer.append($childrenTitle);

        for (const key in structure.children) {
          const child = structure.children[key];

          const $childInfo = $('<div class="ewo-structure-child"></div>');
          $childInfo.append("<h5>" + key + "</h5>");
          $childInfo.append("<p><strong>Type:</strong> " + child.type + "</p>");

          if (child.has_array) {
            $childInfo.append(
              "<p><strong>Contains array:</strong> Yes (length: " +
                child.array_length +
                ")</p>"
            );
            $childInfo.append(
              "<p><strong>Potential service array:</strong> " +
                (child.potential_services ? "Yes" : "No") +
                "</p>"
            );
          }

          $structureContainer.append($childInfo);
        }
      }
    } else {
      $structureContainer.html(
        "<p>No structure information available.</p>"
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
      content = "FOUND SERVICES:\n\n";

      $("#ewo-debug-formatted-services table tbody tr").each(function () {
        const $row = $(this);
        const id = $row.find("td:eq(0)").text();
        const name = $row.find("td:eq(1)").text();
        const price = $row.find("td:eq(2)").text();

        content += `${name} (ID: ${id}) - ${price}\n`;
      });
    } else {
      content = "API RESPONSE STRUCTURE ANALYSIS:\n\n";
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
    $button.text("¡Copied!");

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
      $("#ewo-search-services").text("Searching location...");

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
              "Address not found. Please try another address or use the map to select your location."
            );
          }
        },
        error: function () {
          alert("Error searching for the address. Please try again.");
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
        "Please enter an address or select a point on the map."
      );
    }
  }

  // --- UI CONTROLS ---
  function renderServiceControls(total) {
    const $container = $('#ewo-services-controls');
    $container.empty();
    // Contador
    $container.append(`<div class="ewo-services-count">${total} service${total === 1 ? '' : 's'} found</div>`);
    // Filtros (si está activado)
    if (SHOW_FILTERS) {
      $container.append(`
        <div class="ewo-filters">
          <input type="text" id="ewo-service-search" class="ewo-input" placeholder="Search services..." style="max-width: 220px;">
          <select id="ewo-network-type-filter" class="ewo-input" style="max-width: 180px;">
            <option value="">All Network Types</option>
            <option value="Tarana">Tarana</option>
            <option value="Non-Tarana">Non-Tarana</option>
          </select>
          <select id="ewo-service-ordering" class="ewo-input" style="max-width: 180px;">
            <option value="default">Sort by</option>
            <option value="speed-desc">Speed (High to Low)</option>
            <option value="speed-asc">Speed (Low to High)</option>
            <option value="coverage">Coverage Code</option>
          </select>
        </div>
      `);
    }
    // Selector de columnas (solo en grid)
    if (currentView === 'grid') {
      const colOptions = [2, 3, 4];
      let colSelect = `<div class="ewo-columns-select">Columns: <select id="ewo-columns-select">`;
      colOptions.forEach(opt => {
        colSelect += `<option value="${opt}" ${columns === opt ? 'selected' : ''}>${opt}</option>`;
      });
      colSelect += `</select></div>`;
      $container.append(colSelect);
    }
    // Selector de ítems por página
    const perPageOptions = [4, 8, 12, 16];
    let perPageSelect = `<div class="ewo-per-page-select">Items per page: <select id="ewo-per-page-select">`;
    perPageOptions.forEach(opt => {
      perPageSelect += `<option value="${opt}" ${perPage === opt ? 'selected' : ''}>${opt}</option>`;
    });
    perPageSelect += `</select></div>`;
    $container.append(perPageSelect);
    // Toggle de vista
    $container.append(`
      <div class="ewo-view-toggle">
        <button class="ewo-toggle-btn" data-view="grid" ${currentView === 'grid' ? 'disabled' : ''}>Grid</button>
        <button class="ewo-toggle-btn" data-view="list" ${currentView === 'list' ? 'disabled' : ''}>List</button>
      </div>
    `);
  }

  function applyFiltersAndOrdering() {
    setPreloader(true);
    setTimeout(() => { // Simula async para UX
      let result = [...servicesData];
      // Filtro de búsqueda
      if (searchQuery) {
        const q = searchQuery.toLowerCase();
        result = result.filter(s =>
          (s.coverage_code && s.coverage_code.toLowerCase().includes(q)) ||
          (s.network_type && s.network_type.toLowerCase().includes(q)) ||
          (s.coverage_confidence && s.coverage_confidence.status_text_full && s.coverage_confidence.status_text_full.toLowerCase().includes(q))
        );
      }
      // Filtro por tipo de red
      if (networkTypeFilter) {
        result = result.filter(s => s.network_type === networkTypeFilter);
      }
      // Ordenación
      if (ordering === 'speed-desc') {
        result.sort((a, b) => (b.max_download_speed_mbps || 0) - (a.max_download_speed_mbps || 0));
      } else if (ordering === 'speed-asc') {
        result.sort((a, b) => (a.max_download_speed_mbps || 0) - (b.max_download_speed_mbps || 0));
      } else if (ordering === 'coverage') {
        result.sort((a, b) => {
          if (!a.coverage_code) return 1;
          if (!b.coverage_code) return -1;
          return a.coverage_code.localeCompare(b.coverage_code);
        });
      }
      filteredServices = result;
      currentPage = 1;
      renderServices();
      setPreloader(false);
    }, 200); // Simula carga
  }

  function setPreloader(visible) {
    isLoading = visible;
    if (visible) {
      $('#ewo-loading-services').show();
    } else {
      $('#ewo-loading-services').hide();
    }
  }

  function renderServices() {
    const $servicesList = $("#ewo-services-list");
    $servicesList.empty();
    // Controls
    renderServiceControls(filteredServices.length);
    if (LOAD_MORE) {
      renderLoadMore(filteredServices.length);
    } else {
      renderPagination(filteredServices.length);
    }
    // Pagination/Load more logic
    let startIdx = (currentPage - 1) * perPage;
    let endIdx = startIdx + perPage;
    if (LOAD_MORE) {
      endIdx = currentPage * perPage;
      startIdx = 0;
    }
    const pageServices = filteredServices.slice(startIdx, endIdx);
    // Layout
    $servicesList.removeClass('ewo-grid ewo-list');
    if (currentView === 'grid') {
      $servicesList.addClass('ewo-grid');
      $servicesList.css({
        display: 'grid',
        gridTemplateColumns: `repeat(${columns}, 1fr)`
      });
    } else {
      $servicesList.addClass('ewo-list');
      $servicesList.css({ display: 'block' });
    }
    // Render items
    const $template = $("#ewo-service-template");
    pageServices.forEach(function (service) {
      const $serviceItem = $($template.html());
      $serviceItem
        .find(".ewo-service-name")
        .text(
          service.coverage_code
            ? `Coverage: ${service.coverage_code} (${service.network_type || ''})`
            : "Unnamed service"
        );
      $serviceItem.find(".ewo-service-price").text(
        service.max_download_speed_mbps
          ? `Speed: ${service.max_download_speed_mbps} Mbps`
          : ""
      );
      $serviceItem
        .find(".ewo-service-description")
        .text(
          service.coverage_confidence && service.coverage_confidence.status_text_full
            ? service.coverage_confidence.status_text_full
            : "No description"
        );
      $serviceItem
        .find(".ewo-select-service")
        .attr("data-service-id", service.id);
      $serviceItem.attr("data-service-json", JSON.stringify(service));
      // Card color usage
      if (CARD_COLOR_USAGE !== 'none' && service.coverage_confidence && service.coverage_confidence.status_color_hex) {
        if (CARD_COLOR_USAGE === 'border') {
          $serviceItem.css({
            'border': '2px solid ' + service.coverage_confidence.status_color_hex,
            'box-shadow': '0 0 0 1px ' + service.coverage_confidence.status_color_hex
          });
        } else if (CARD_COLOR_USAGE === 'background') {
          $serviceItem.css('background-color', service.coverage_confidence.status_color_hex);
        }
      }
      $servicesList.append($serviceItem);
    });
  }

  function renderPagination(total) {
    const $container = $('#ewo-services-pagination');
    $container.empty();
    if (!SHOW_PAGINATION || LOAD_MORE) return;
    const totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) return;
    let html = '<div class="ewo-pagination">';
    if (currentPage > 1) {
      html += `<button class="ewo-page-btn" data-page="${currentPage - 1}">Previous</button>`;
    }
    for (let i = 1; i <= totalPages; i++) {
      html += `<button class="ewo-page-btn${i === currentPage ? ' active' : ''}" data-page="${i}">${i}</button>`;
    }
    if (currentPage < totalPages) {
      html += `<button class="ewo-page-btn" data-page="${currentPage + 1}">Next</button>`;
    }
    html += '</div>';
    $container.html(html);
  }

  function renderLoadMore(total) {
    const $container = $('#ewo-services-pagination');
    $container.empty();
    if (!LOAD_MORE) return;
    const totalPages = Math.ceil(total / perPage);
    if ((currentPage * perPage) < total) {
      $container.html(`<div class="ewo-load-more-container"><button class="ewo-load-more-btn">Load more</button></div>`);
    }
  }
})(jQuery);