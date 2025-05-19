(function ($) {
  "use strict";

  // Estado global de coordenadas y dirección
  window.structuredAddress = window.structuredAddress || {
    address_line_one: '',
    city: '',
    state: '',
    zip: ''
  };

  let map = null;
  let marker = null;

  // Inicializa el mapa y marcador
  window.initEwoMap = function() {
    const initialLat = 37.806479687628936;
    const initialLng = -89.07903653069094;
    map = L.map("ewo-map-container").setView([initialLat, initialLng], 13);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);
    marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    marker.on("dragend", function (event) {
      const position = marker.getLatLng();
      updateCoordinates(position.lat, position.lng);
    });
    map.on("click", function (event) {
      marker.setLatLng(event.latlng);
      updateCoordinates(event.latlng.lat, event.latlng.lng);
      reverseGeocode(event.latlng.lat, event.latlng.lng);
      $("#ewo-search-services").addClass("ewo-button-highlighted");
      setTimeout(function () {
        $("#ewo-search-services").removeClass("ewo-button-highlighted");
      }, 1500);
    });
    setTimeout(function () { map.invalidateSize(); }, 100);
  };

  // Actualiza los campos ocultos y localStorage
  function updateCoordinates(lat, lng) {
    $("#ewo-latitude").val(lat);
    $("#ewo-longitude").val(lng);
    localStorage.setItem('ewo_latitude', lat);
    localStorage.setItem('ewo_longitude', lng);
  }

  // Geolocalización del navegador
  window.getUserLocation = function(e) {
    e.preventDefault();
    if (navigator.geolocation) {
      $("#ewo-use-my-location").prop("disabled", true).html('<span class="dashicons dashicons-location"></span> Locating...');
      navigator.geolocation.getCurrentPosition(
        function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          map.setView([lat, lng], 15);
          marker.setLatLng([lat, lng]);
          updateCoordinates(lat, lng);
          reverseGeocode(lat, lng);
          $("#ewo-use-my-location").prop("disabled", false).html('<span class="dashicons dashicons-location"></span> Use my location');
          $("#ewo-search-services").addClass("ewo-button-highlighted");
          setTimeout(function () { $("#ewo-search-services").removeClass("ewo-button-highlighted"); }, 1500);
        },
        function (error) {
          alert("Error getting your location.");
          $("#ewo-use-my-location").prop("disabled", false).html('<span class="dashicons dashicons-location"></span> Use my location');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    } else {
      alert("Your browser does not support geolocation.");
    }
  };

  // Geocodificación inversa
  function reverseGeocode(lat, lng) {
    $.ajax({
      url: "https://nominatim.openstreetmap.org/reverse",
      type: "GET",
      data: { lat: lat, lon: lng, format: "json", addressdetails: 1 },
      success: function (data) {
        if (data && data.display_name) {
          $("#ewo-address-input").val(data.display_name);
        }
        extractStructuredAddressFromNominatim(data);
      }
    });
  }

  // Extraer dirección estructurada y sincronizar
  function extractStructuredAddressFromNominatim(data) {
    if (!data || !data.address) return;
    const addr = {
      address_line_one: (data.address.road || '') + (data.address.house_number ? ' ' + data.address.house_number : ''),
      city: data.address.city || data.address.town || data.address.village || '',
      state: data.address.state || '',
      zip: data.address.postcode || ''
    };
    window.structuredAddress = addr;
    localStorage.setItem('ewo_structured_address', JSON.stringify(addr));
  }

  // Autocompletado de dirección (solo Nominatim por simplicidad)
  window.initAddressAutocomplete = function() {
    const input = document.getElementById('ewo-address-input');
    if (!input) return;
    $(input).off();
    if ($.ui && $.ui.autocomplete) {
      if ($.data(input, 'ui-autocomplete')) {
        $(input).autocomplete('destroy');
      }
      $(input).autocomplete({
        minLength: 3,
        source: function(request, response) {
          $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            dataType: 'json',
            data: {
              q: request.term,
              format: 'json',
              addressdetails: 1,
              countrycodes: 'us',
              'accept-language': 'en',
              limit: 5
            },
            success: function(data) {
              response($.map(data, function(item) {
                return {
                  label: item.display_name,
                  value: item.display_name,
                  lat: item.lat,
                  lon: item.lon
                };
              }));
            }
          });
        },
        select: function(event, ui) {
          $(input).val(ui.item.value);
          updateCoordinates(ui.item.lat, ui.item.lon);
          reverseGeocode(ui.item.lat, ui.item.lon);
          return false;
        }
      });
    }
  };

  // Avanzar al paso de servicios
  window.showServicesSection = function() {
    $(".ewo-section").removeClass("active");
    $("#ewo-available-services-section").addClass("active");
    if (typeof setActiveStep === 'function') setActiveStep(2);
  };

  // Inicialización al cargar el DOM
  $(document).ready(function () {
    if ($("#ewo-map-container").length) {
      window.initEwoMap();
    }
    window.initAddressAutocomplete();
    // Evento para botón de geolocalización
    $(document).on("click", "#ewo-use-my-location", window.getUserLocation);
    // Evento para buscar servicios
    $(document).on("click", "#ewo-search-services", function(e) {
      e.preventDefault();
      // Validar que haya coordenadas
      const lat = $("#ewo-latitude").val().trim();
      const lng = $("#ewo-longitude").val().trim();
      if (!lat || !lng) {
        alert("Please select a location on the map or enter an address first.");
        return;
      }
      window.showServicesSection();
    });
    // Manejar enter en input de dirección
    $(document).on("keypress", "#ewo-address-input", function (e) {
      if (e.which === 13) {
        e.preventDefault();
        // Simular click en buscar servicios
        $("#ewo-search-services").trigger("click");
      }
    });
  });

})(jQuery); 