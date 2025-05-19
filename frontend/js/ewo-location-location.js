(function ($) {
  "use strict";

  // Estado global de coordenadas y dirección
  window.structuredAddress = window.structuredAddress || {
    address_line_one: '',
    city: '',
    state: '',
    zip: ''
  };

  let mapProvider = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.map_provider) ? window.ewoServiceListingOptions.map_provider : 'osm';
  let map = null;
  let marker = null;
  let googleMap = null;
  let googleMarker = null;

  // Función para cargar el script de Google Maps si es necesario
  function loadGoogleMapsScript(callback) {
    if (window.google && window.google.maps && typeof google.maps.Map === 'function') {
      callback();
      return;
    }
    const apiKey = window.ewoServiceListingOptions && window.ewoServiceListingOptions.map_google_api_key ? window.ewoServiceListingOptions.map_google_api_key : '';
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&language=en&region=US`;
    script.async = true;
    script.defer = true;
    script.onload = callback;
    document.head.appendChild(script);
  }

  // Inicializar mapa según proveedor
  window.initEwoMap = function() {
    if (mapProvider === 'google') {
      loadGoogleMapsScript(function() {
        const initialLat = 37.806479687628936;
        const initialLng = -89.07903653069094;
        const mapOptions = {
          center: { lat: initialLat, lng: initialLng },
          zoom: 13,
          mapTypeId: 'roadmap',
          streetViewControl: false,
          fullscreenControl: false
        };
        googleMap = new google.maps.Map(document.getElementById('ewo-map-container'), mapOptions);
        googleMarker = new google.maps.Marker({
          position: { lat: initialLat, lng: initialLng },
          map: googleMap,
          draggable: true
        });
        google.maps.event.addListener(googleMarker, 'dragend', function(event) {
          updateCoordinates(event.latLng.lat(), event.latLng.lng());
        });
        google.maps.event.addListener(googleMap, 'click', function(event) {
          googleMarker.setPosition(event.latLng);
          updateCoordinates(event.latLng.lat(), event.latLng.lng());
          reverseGeocode(event.latLng.lat(), event.latLng.lng());
          $("#ewo-search-services").addClass("ewo-button-highlighted");
          setTimeout(function () {
            $("#ewo-search-services").removeClass("ewo-button-highlighted");
          }, 1500);
        });
      });
    } else {
      // OpenStreetMap/Leaflet (por defecto)
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
    }
  };

  // Función para centrar el mapa y el marcador
  function setMapLocation(lat, lng, zoom) {
    if (mapProvider === 'google' && googleMap && googleMarker) {
      googleMap.setCenter({ lat: parseFloat(lat), lng: parseFloat(lng) });
      if (zoom) googleMap.setZoom(zoom);
      googleMarker.setPosition({ lat: parseFloat(lat), lng: parseFloat(lng) });
    } else if (map && marker) {
      map.setView([lat, lng], zoom || 15);
      marker.setLatLng([lat, lng]);
    }
  }

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
      data: { 
        lat: lat, 
        lon: lng, 
        format: "json", 
        addressdetails: 1,
        'accept-language': 'en'
      },
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

  // Modificar setLatLngFromAutocomplete para ambos proveedores
  function setLatLngFromAutocomplete(lat, lon) {
    $('#ewo-latitude').val(lat);
    $('#ewo-longitude').val(lon);
    localStorage.setItem('ewo_latitude', lat);
    localStorage.setItem('ewo_longitude', lon);
    setMapLocation(lat, lon, 15);
  }

  // REEMPLAZADA: Autocompletado de dirección con todos los proveedores
  window.initAddressAutocomplete = function() {
    const input = document.getElementById('ewo-address-input');
    if (!input) return;

    const autocompleteProvider = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.autocomplete_provider) ? window.ewoServiceListingOptions.autocomplete_provider : 'nominatim';
    const autocompleteApiKey = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.autocomplete_api_key) ? window.ewoServiceListingOptions.autocomplete_api_key : '';

    // Limpieza de instancias/listeners previos
    $(input).off();
    if ($.ui && $.ui.autocomplete && $(input).data('ui-autocomplete')) {
        $(input).autocomplete('destroy');
      }
    $(input).siblings('.mapboxgl-ctrl-geocoder').remove();
    // Asegurarse que el input original esté visible por defecto (Mapbox lo oculta)
    $(input).show();

    if (autocompleteProvider === 'nominatim') {
      if ($.ui && $.ui.autocomplete) {
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
            setLatLngFromAutocomplete(ui.item.lat, ui.item.lon);
            if (typeof reverseGeocode === 'function') {
          reverseGeocode(ui.item.lat, ui.item.lon);
            }
          return false;
          }
        });
      }
    } else if (autocompleteProvider === 'google') {
      function loadGooglePlacesScript(callback) {
        if (window.google && window.google.maps && window.google.maps.places && typeof google.maps.places.Autocomplete === 'function') {
          callback();
          return;
        }
        const apiKey = window.ewoServiceListingOptions && window.ewoServiceListingOptions.google_autocomplete_api_key ? window.ewoServiceListingOptions.google_autocomplete_api_key : '';
        const existingScript = document.querySelector('script[src*="maps.googleapis.com/maps/api/js"]');
        if (existingScript && !existingScript.hasAttribute('data-loading-complete')) {
            existingScript.addEventListener('load', () => {
                existingScript.setAttribute('data-loading-complete', 'true');
                checkGooglePlacesReady(callback, 10, 200);
            });
            return;
        }
        if (existingScript) {
            checkGooglePlacesReady(callback, 10, 200);
            return;
        }
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&language=en&region=US`;
        script.async = true;
        script.onload = function() {
          script.setAttribute('data-loading-complete', 'true');
          checkGooglePlacesReady(callback, 10, 200);
        };
        document.head.appendChild(script);
      }

      function checkGooglePlacesReady(callback, maxRetries, interval) {
        let retries = 0;
        function attempt() {
          if (window.google && window.google.maps && window.google.maps.places && typeof google.maps.places.Autocomplete === 'function') {
            callback();
          } else {
            retries++;
            if (retries < maxRetries) {
              setTimeout(attempt, interval);
            } else {
              console.error('Google Maps Places Autocomplete did not become available after multiple retries.');
            }
          }
        }
        attempt();
      }

      loadGooglePlacesScript(function() {
        const autocomplete = new google.maps.places.Autocomplete(input, {
          types: ['address'],
          componentRestrictions: { country: 'us' },
        });
        autocomplete.setFields(['formatted_address', 'geometry']);
        autocomplete.addListener('place_changed', function() {
          const place = autocomplete.getPlace();
          if (place && place.geometry && place.geometry.location) {
            const lat = place.geometry.location.lat();
            const lon = place.geometry.location.lng();
            setLatLngFromAutocomplete(lat, lon);
            $(input).val(place.formatted_address);
            if (typeof reverseGeocode === 'function') {
                reverseGeocode(lat, lon);
            }
          }
        });
      });
    } else if (autocompleteProvider === 'mapbox') {
      function loadMapboxScript(callback) {
        if (window.MapboxGeocoder) {
          callback();
          return;
        }
        const apiKey = window.ewoServiceListingOptions && window.ewoServiceListingOptions.mapbox_autocomplete_api_key ? window.ewoServiceListingOptions.mapbox_autocomplete_api_key : '';
        const script = document.createElement('script');
        script.src = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js';
        script.onload = function() {
          const geocoderScript = document.createElement('script');
          geocoderScript.src = 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.2/mapbox-gl-geocoder.min.js';
          geocoderScript.onload = callback;
          document.head.appendChild(geocoderScript);
        };
        document.head.appendChild(script);
        // CSS
        const css1 = document.createElement('link');
        css1.rel = 'stylesheet';
        css1.href = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css';
        document.head.appendChild(css1);
        const css2 = document.createElement('link');
        css2.rel = 'stylesheet';
        css2.href = 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.2/mapbox-gl-geocoder.css';
        document.head.appendChild(css2);
      }
      loadMapboxScript(function() {
        const apiKey = window.ewoServiceListingOptions && window.ewoServiceListingOptions.mapbox_autocomplete_api_key ? window.ewoServiceListingOptions.mapbox_autocomplete_api_key : '';
        mapboxgl.accessToken = apiKey;
        const geocoder = new MapboxGeocoder({
          accessToken: apiKey,
          types: 'address',
          countries: 'us',
          language: 'en',
          placeholder: 'Enter your address',
          mapboxgl: mapboxgl,
        });
        
        const geocoderContainer = document.createElement('div');
        input.parentNode.insertBefore(geocoderContainer, input); // Insertar antes para que input esté debajo
        $(input).hide(); // Ocultar el input nuestro y usar el de mapbox
        geocoder.addTo(geocoderContainer);
        
        geocoder.on('result', function(e) {
          $(input).show(); // Mostrar nuestro input de nuevo
          $(geocoderContainer).remove(); // Mejor eliminar el contenedor del geocoder de mapbox

          if (e.result && e.result.geometry && e.result.geometry.coordinates) {
            const lon = e.result.geometry.coordinates[0]; const lat = e.result.geometry.coordinates[1];
            setLatLngFromAutocomplete(lat, lon);
            $(input).val(e.result.place_name);
            if (typeof reverseGeocode === 'function') reverseGeocode(lat, lon);
          }
        });
        geocoder.on('clear', function() { 
          $(input).val(''); 
          $(input).show(); // Mostrar nuestro input si se limpia el geocoder
          $(geocoderContainer).remove();
        });
      });
    } else if (autocompleteProvider === 'algolia') {
      function loadAlgoliaScript(callback) {
        if (window.places) { callback(); return; }
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/places.js@1.19.0';
        script.async = true; script.onload = callback; document.head.appendChild(script);
      }
      loadAlgoliaScript(function() {
        const tryInitAlgolia = () => {
          if (window.places) {
            const placesAutocomplete = places({ container: input, countries: ['us'], language: 'en' });
            placesAutocomplete.on('change', function(e) {
              if (e.suggestion && e.suggestion.latlng) {
                setLatLngFromAutocomplete(e.suggestion.latlng.lat, e.suggestion.latlng.lng);
                $(input).val(e.suggestion.value);
                if (typeof reverseGeocode === 'function') reverseGeocode(e.suggestion.latlng.lat, e.suggestion.latlng.lng);
              }
            });
            placesAutocomplete.on('clear', function() { $(input).val(''); });
          } else { setTimeout(tryInitAlgolia, 200); }
        };
        tryInitAlgolia();
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