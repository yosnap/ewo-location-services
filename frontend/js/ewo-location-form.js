// Multistep form in vanilla JS with hero, synchronized inputs, dynamic overlay, and full address autocomplete/geocoding

(function() {
  const root = document.getElementById('ewo-location-root');
  if (!root) return;

  let currentStep = 1;
  let map, marker;
  // Config from backend (set in PHP):
  // window.ewoLocationConfig = { autocompleteProvider: 'google'|'mapbox'|'nominatim', googleApiKey: '', mapboxApiKey: '' }
  const config = window.ewoLocationConfig || { autocompleteProvider: 'nominatim' };

  // --- Helper: Show/Hide overlay and sync inputs ---
  function showOverlay(address) {
    const overlay = document.querySelector('.ewo-map-overlay-form');
    overlay.style.display = 'flex';
    updateModalAddressText(address);
    document.getElementById('ewo-hero-address-input').value = address;
  }
  function hideOverlay() {
    const overlay = document.querySelector('.ewo-map-overlay-form');
    overlay.style.display = 'none';
  }
  function isLatLng(str) {
    // Simple check for "lat, lng"
    return /^-?\d{1,3}\.?\d*,\s*-?\d{1,3}\.?\d*$/.test(str.trim());
  }

  // --- Helper: Save coordinates to localStorage ---
  function saveCoordsAndReverseGeocode(lat, lng) {
    localStorage.setItem('ewo-location-coords', JSON.stringify({ lat, lng }));
    reverseGeocodeAndSave(lat, lng);
  }

  // --- Reverse geocoding y guardado de dirección estructurada ---
  function reverseGeocodeAndSave(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
      .then(res => res.json())
      .then(data => {
        if (data && data.address) {
          const structured = {
            address_line_one: (data.address.road || '') + (data.address.house_number ? ' ' + data.address.house_number : ''),
            city: data.address.city || data.address.town || data.address.village || '',
            state: data.address.state || '',
            zip: data.address.postcode || ''
          };
          localStorage.setItem('ewo_structured_address', JSON.stringify(structured));
          // Precargar en el formulario si ya está presente
          if (document.querySelector('[name="address_line_one"]')) {
            document.querySelector('[name="address_line_one"]').value = structured.address_line_one;
          }
          if (document.querySelector('[name="city"]')) {
            document.querySelector('[name="city"]').value = structured.city;
          }
          if (document.querySelector('[name="state"]')) {
            document.querySelector('[name="state"]').value = structured.state;
          }
          if (document.querySelector('[name="zip"]')) {
            document.querySelector('[name="zip"]').value = structured.zip;
          }
        }
      });
  }

  // --- Geocoding: Address to LatLng ---
  async function geocodeAddress(address) {
    if (config.autocompleteProvider === 'google' && config.googleApiKey) {
      // Google Geocoding API
      const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${config.googleApiKey}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.status === 'OK' && data.results[0]) {
        const loc = data.results[0].geometry.location;
        return { lat: loc.lat, lng: loc.lng, formatted: data.results[0].formatted_address };
      }
    } else if (config.autocompleteProvider === 'mapbox' && config.mapboxApiKey) {
      // Mapbox Geocoding API
      const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${config.mapboxApiKey}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.features && data.features[0]) {
        const [lng, lat] = data.features[0].center;
        return { lat, lng, formatted: data.features[0].place_name };
      }
    } else {
      // Nominatim (OpenStreetMap)
      const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data[0]) {
        return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon), formatted: data[0].display_name };
      }
    }
    return null;
  }

  // --- Reverse Geocoding: LatLng to Address ---
  async function reverseGeocode(lat, lng) {
    if (config.autocompleteProvider === 'google' && config.googleApiKey) {
      const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${config.googleApiKey}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.status === 'OK' && data.results[0]) {
        return data.results[0].formatted_address;
      }
    } else if (config.autocompleteProvider === 'mapbox' && config.mapboxApiKey) {
      const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${config.mapboxApiKey}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.features && data.features[0]) {
        return data.features[0].place_name;
      }
    } else {
      const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.display_name) {
        return data.display_name;
      }
    }
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
  }

  // --- Autocomplete Initialization ---
  function initAutocomplete(inputId, onSelect) {
    const input = document.getElementById(inputId);
    let acInstance = null;
    if (config.autocompleteProvider === 'google' && window.google && window.google.maps && window.google.maps.places) {
      // Google Places Autocomplete
      acInstance = new google.maps.places.Autocomplete(input);
      acInstance.addListener('place_changed', async function() {
        const place = acInstance.getPlace();
        if (place.geometry && place.geometry.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();
          onSelect({ lat, lng, formatted: place.formatted_address });
        }
      });
    } else if (config.autocompleteProvider === 'mapbox' && config.mapboxApiKey) {
      // Mapbox Autocomplete (simple fetch, not full widget)
      input.addEventListener('input', async function() {
        const val = input.value;
        if (val.length < 3) return;
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(val)}.json?access_token=${config.mapboxApiKey}`;
        const res = await fetch(url);
        const data = await res.json();
        let datalist = document.getElementById(inputId + '-datalist');
        if (!datalist) {
          datalist = document.createElement('datalist');
          datalist.id = inputId + '-datalist';
          document.body.appendChild(datalist);
          input.setAttribute('list', datalist.id);
        }
        datalist.innerHTML = '';
        if (data.features) {
          data.features.forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.place_name;
            datalist.appendChild(opt);
          });
        }
      });
      input.addEventListener('change', async function() {
        const val = input.value;
        const geo = await geocodeAddress(val);
        if (geo) onSelect(geo);
      });
    } else {
      // Nominatim Autocomplete (simple fetch, not full widget)
      input.addEventListener('input', async function() {
        const val = input.value;
        if (val.length < 3) return;
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(val)}`;
        const res = await fetch(url);
        const data = await res.json();
        let datalist = document.getElementById(inputId + '-datalist');
        if (!datalist) {
          datalist = document.createElement('datalist');
          datalist.id = inputId + '-datalist';
          document.body.appendChild(datalist);
          input.setAttribute('list', datalist.id);
        }
        datalist.innerHTML = '';
        data.forEach(f => {
          const opt = document.createElement('option');
          opt.value = f.display_name;
          datalist.appendChild(opt);
        });
      });
      input.addEventListener('change', async function() {
        const val = input.value;
        const geo = await geocodeAddress(val);
        if (geo) onSelect(geo);
      });
    }
  }

  // --- Load Google Maps script (with Places if needed) ---
  function loadGoogleMapsScriptUnified(cb) {
    if (window.google && window.google.maps && window.google.maps.places) return cb();
    if (document.getElementById('ewo-google-maps-script')) {
      // Script is loading, wait for it
      document.getElementById('ewo-google-maps-script').addEventListener('load', cb);
      return;
    }
    const script = document.createElement('script');
    script.id = 'ewo-google-maps-script';
    // Always load with libraries=places if either provider is google
    let src = `https://maps.googleapis.com/maps/api/js?key=${config.googleApiKey}`;
    if ((config.mapProvider === 'google' || config.autocompleteProvider === 'google') && config.googleApiKey) {
      src += '&libraries=places';
    }
    script.src = src;
    script.onload = cb;
    document.body.appendChild(script);
  }

  // --- Main render functions ---
  function renderHero() {
    const featuredImage = root.getAttribute('data-featured-image');
    const hero = document.createElement('div');
    hero.className = 'ewo-hero';
    hero.innerHTML = `
      <div class="ewo-hero-bg" style="background-image:url('${featuredImage}');"></div>
      <div class="ewo-hero-overlay"></div>
      <div class="ewo-hero-content">
        <h1 class="ewo-hero-title">Check Availability in Your Area</h1>
        <p class="ewo-hero-desc">Enter your address below to see if we offer services in your area.</p>
        <form class="ewo-hero-form" autocomplete="off" onsubmit="return false;">
          <div class="ewo-hero-input-group">
            <input type="text" id="ewo-hero-address-input" class="ewo-input ewo-autocomplete" placeholder="123, Street Name, City, State, Zip Code" autocomplete="off" />
            <button type="button" id="ewo-hero-geolocate" class="ewo-geolocate-btn" title="Use my location">📍</button>
            <button type="submit" id="ewo-hero-check" class="ewo-btn ewo-btn-primary">Check Availability</button>
          </div>
        </form>
        <div id="ewo-form-container"></div>
      </div>
    `;
    root.appendChild(hero);
    // Assign event after element exists
    document.getElementById('ewo-hero-check').onclick = handleCheckAvailabilityClick;
  }

  function renderBannerMapForm() {
    const banner = document.createElement('div');
    banner.className = 'ewo-banner';
    banner.innerHTML = `
      <h1 class="ewo-banner-title">Let's Pin Down Your Location for Best Results</h1>
      <p class="ewo-banner-subtitle">Please drag and place the pin to the roof of the structure where you need internet. This helps us determine the strength of the signal available to you!</p>
    `;
    root.appendChild(banner);

    const mapSection = document.createElement('div');
    mapSection.className = 'ewo-map-section';
    mapSection.innerHTML = `
      <div id="ewo-map-container" class="ewo-map-container"></div>
      <div class="ewo-map-overlay-form" style="display:none;">
        <div class="ewo-map-address-box">
          <label for="ewo-map-address-input" class="ewo-address-label">Address:</label>
          <input type="text" id="ewo-map-address-input" class="ewo-input ewo-autocomplete" placeholder="123, Street Name, City, State, Zip Code" autocomplete="off" readonly />
          <button type="button" id="ewo-map-geolocate" class="ewo-geolocate-btn" title="Use my location">📍</button>
          <button id="ewo-map-check-availability" class="ewo-btn ewo-btn-primary">Check Availability</button>
        </div>
      </div>
    `;
    root.appendChild(mapSection);
    // Assign event after element exists
    document.getElementById('ewo-map-check-availability').onclick = handleCheckAvailabilityClick;

    // --- MAPA GOOGLE O LEAFLET SEGÚN CONFIG ---
    function initGoogleMap() {
      // Try to load coordinates from localStorage
      let defaultLat = 37.806542219006026;
      let defaultLng = -89.07865047454835;
      let defaultZoom = config.mapZoom || 15;
      const savedCoords = localStorage.getItem('ewo-location-coords');
      if (savedCoords) {
        try {
          const { lat, lng } = JSON.parse(savedCoords);
          if (typeof lat === 'number' && typeof lng === 'number') {
            defaultLat = lat;
            defaultLng = lng;
            defaultZoom = config.mapZoom || 15;
          }
        } catch (e) {}
      }
      const mapOptions = {
        center: { lat: defaultLat, lng: defaultLng },
        zoom: defaultZoom,
        mapTypeId: 'roadmap',
        streetViewControl: false,
        fullscreenControl: false
      };
      map = new google.maps.Map(document.getElementById('ewo-map-container'), mapOptions);
      marker = new google.maps.Marker({
        position: { lat: defaultLat, lng: defaultLng },
        map: map,
        draggable: true
      });
      marker.addListener('dragend', async function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        const address = await reverseGeocode(lat, lng);
        showOverlay(address);
        saveCoordsAndReverseGeocode(lat, lng);
      });
      map.addListener('click', async function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        marker.setPosition({ lat, lng });
        map.setCenter({ lat, lng });
        map.setZoom(config.mapZoom || 15);
        const address = await reverseGeocode(lat, lng);
        showOverlay(address);
        saveCoordsAndReverseGeocode(lat, lng);
      });
      // Geolocate from overlay
      document.getElementById('ewo-map-geolocate').onclick = function(e) {
        e.preventDefault();
        if (!navigator.geolocation) {
          alert('Geolocation is not supported by your browser.');
          return;
        }
        if (!map || !marker) {
          alert('The map is not ready yet. Please wait a moment and try again.');
          return;
        }
        navigator.geolocation.getCurrentPosition(async function(pos) {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          if (config.mapProvider === 'google' && typeof marker.setPosition === 'function') {
            marker.setPosition({ lat, lng });
            map.setCenter({ lat, lng });
            map.setZoom(config.mapZoom || 15);
          } else if (typeof marker.setLatLng === 'function') {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], config.mapZoom || 15);
          }
          const address = await reverseGeocode(lat, lng);
          showOverlay(address);
          saveCoordsAndReverseGeocode(lat, lng);
        }, function() {
          alert('Unable to retrieve your location.');
        });
      };
    }
    function loadLeafletAssets(callback) {
      if (!document.getElementById('leaflet-css')) {
        const link = document.createElement('link');
        link.id = 'leaflet-css';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
      }
      if (typeof window.L === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = callback;
        document.body.appendChild(script);
      } else {
        callback();
      }
    }
    function initLeafletMap() {
      // Try to load coordinates from localStorage
      let defaultLat = 37.806542219006026;
      let defaultLng = -89.07865047454835;
      let defaultZoom = config.mapZoom || 15;
      const savedCoords = localStorage.getItem('ewo-location-coords');
      if (savedCoords) {
        try {
          const { lat, lng } = JSON.parse(savedCoords);
          if (typeof lat === 'number' && typeof lng === 'number') {
            defaultLat = lat;
            defaultLng = lng;
            defaultZoom = config.mapZoom || 15;
          }
        } catch (e) {}
      }
      map = L.map('ewo-map-container', {zoomControl: true}).setView([defaultLat, defaultLng], defaultZoom);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);
      marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
      marker.on('dragend', async function(e) {
        const latlng = marker.getLatLng();
        const address = await reverseGeocode(latlng.lat, latlng.lng);
        showOverlay(address);
        saveCoordsAndReverseGeocode(latlng.lat, latlng.lng);
      });
      // Allow selecting location by clicking on the map
      map.on('click', async function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], config.mapZoom || 15);
        const address = await reverseGeocode(lat, lng);
        showOverlay(address);
        saveCoordsAndReverseGeocode(lat, lng);
      });
      // Geolocate from overlay
      document.getElementById('ewo-map-geolocate').onclick = function(e) {
        e.preventDefault();
        if (!navigator.geolocation) {
          alert('Geolocation is not supported by your browser.');
          return;
        }
        if (!map || !marker) {
          alert('The map is not ready yet. Please wait a moment and try again.');
          return;
        }
        navigator.geolocation.getCurrentPosition(async function(pos) {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          if (config.mapProvider === 'google' && typeof marker.setPosition === 'function') {
            marker.setPosition({ lat, lng });
            map.setCenter({ lat, lng });
            map.setZoom(config.mapZoom || 15);
          } else if (typeof marker.setLatLng === 'function') {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], config.mapZoom || 15);
          }
          const address = await reverseGeocode(lat, lng);
          showOverlay(address);
          saveCoordsAndReverseGeocode(lat, lng);
        }, function() {
          alert('Unable to retrieve your location.');
        });
      };
    }
    // Decide which map to load
    if (config.mapProvider === 'google' && config.googleApiKey) {
      loadGoogleMapsScriptUnified(function() {
        initGoogleMap();
        // Si autocomplete también es Google, inicializarlo aquí
        if (config.autocompleteProvider === 'google') {
          initAutocomplete('ewo-hero-address-input', onSelectAutocomplete);
          initAutocomplete('ewo-map-address-input', onSelectAutocomplete);
        }
      });
    } else {
      loadLeafletAssets(function() {
        initLeafletMap();
        // Si autocomplete es Google, cargar script solo para autocomplete
        if (config.autocompleteProvider === 'google' && config.googleApiKey) {
          loadGoogleMapsScriptUnified(function() {
            initAutocomplete('ewo-hero-address-input', onSelectAutocomplete);
            initAutocomplete('ewo-map-address-input', onSelectAutocomplete);
          });
        } else {
          initAutocomplete('ewo-hero-address-input', onSelectAutocomplete);
          initAutocomplete('ewo-map-address-input', onSelectAutocomplete);
        }
      });
    }

    // Geolocate from hero
    document.getElementById('ewo-hero-geolocate').onclick = function(e) {
      e.preventDefault();
      if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
      }
      if (!map || !marker) {
        alert('The map is not ready yet. Please wait a moment and try again.');
        return;
      }
      navigator.geolocation.getCurrentPosition(async function(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (config.mapProvider === 'google' && typeof marker.setPosition === 'function') {
          marker.setPosition({ lat, lng });
          map.setCenter({ lat, lng });
          map.setZoom(config.mapZoom || 15);
        } else if (typeof marker.setLatLng === 'function') {
          marker.setLatLng([lat, lng]);
          map.setView([lat, lng], config.mapZoom || 15);
        }
        const address = await reverseGeocode(lat, lng);
        showOverlay(address);
        saveCoordsAndReverseGeocode(lat, lng);
      }, function() {
        alert('Unable to retrieve your location.');
      });
    };
    // Check Availability from hero
    document.getElementById('ewo-hero-check').onclick = async function(e) {
      e.preventDefault();
      const value = document.getElementById('ewo-hero-address-input').value.trim();
      if (!value) {
        alert('Please enter your address or coordinates.');
        return;
      }
      let lat, lng;
      if (isLatLng(value) && map && marker) {
        // If lat,lng, move marker and show overlay
        const parts = value.split(',');
        lat = parseFloat(parts[0]);
        lng = parseFloat(parts[1]);
        if (config.mapProvider === 'google' && typeof marker.setPosition === 'function') {
          marker.setPosition({lat: lat, lng: lng});
          map.setCenter({lat: lat, lng: lng});
          map.setZoom(15);
        } else if (typeof marker.setLatLng === 'function') {
          marker.setLatLng([lat, lng]);
          map.setView([lat, lng], 15);
        }
        const address = await reverseGeocode(lat, lng);
        showOverlay(address);
        saveCoordsAndReverseGeocode(lat, lng);
      } else {
        // Geocode address
        const geo = await geocodeAddress(value);
        if (geo && map && marker) {
          lat = geo.lat;
          lng = geo.lng;
          if (config.mapProvider === 'google' && typeof marker.setPosition === 'function') {
            marker.setPosition({lat: geo.lat, lng: geo.lng});
            map.setCenter({lat: geo.lat, lng: geo.lng});
            map.setZoom(15);
          } else if (typeof marker.setLatLng === 'function') {
            marker.setLatLng([geo.lat, geo.lng]);
            map.setView([geo.lat, geo.lng], 15);
          }
          showOverlay(geo.formatted);
          saveCoordsAndReverseGeocode(lat, lng);
        } else {
          alert('Address not found.');
          return;
        }
      }
      // Llamar a submitCoverageCheck con las coordenadas actuales
      if (typeof lat !== 'undefined' && typeof lng !== 'undefined') {
        await submitCoverageCheck(lat, lng);
      }
    };
    // Autocomplete for both inputs
    function onSelectAutocomplete(geo) {
      if (geo && map && marker) {
        if (config.mapProvider === 'google' && typeof marker.setPosition === 'function') {
          marker.setPosition({ lat: geo.lat, lng: geo.lng });
          map.setCenter({ lat: geo.lat, lng: geo.lng });
          map.setZoom(config.mapZoom || 15);
        } else if (typeof marker.setLatLng === 'function') {
          marker.setLatLng([geo.lat, geo.lng]);
          map.setView([geo.lat, geo.lng], config.mapZoom || 15);
        }
        showOverlay(geo.formatted);
        saveCoordsAndReverseGeocode(geo.lat, geo.lng);
      }
    }
  }

  function renderStep() {
    root.innerHTML = '';
    renderHero();
    if (currentStep === 1) {
      renderBannerMapForm();
    }
  }

  // --- Helper: Submit lat/lng to backend for coverage check ---
  async function submitCoverageCheck(lat, lng) {
    // Petición AJAX a WordPress para evitar CORS
    let status = null;
    try {
      const formData = new FormData();
      formData.append('action', 'ewo_check_coverage');
      formData.append('lat', lat);
      formData.append('lng', lng);
      const res = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      console.log('Respuesta AJAX:', data); // Debug
      // Extraer status de varias formas posibles
      if (data && data.success) {
        // Guardar coverage_code si existe
        let code = null;
        if (data.data && data.data.raw) {
          code = data.data.raw['return-data'] &&
                 data.data.raw['return-data']['serviceability-info'] &&
                 data.data.raw['return-data']['serviceability-info']['coverage_code'];
          if (code) {
            localStorage.setItem('ewo-coverage-code', code);
          }
        }
        if (data.data && data.data.status) {
          status = data.data.status.toLowerCase();
        } else if (data.status) {
          status = data.status.toLowerCase();
        }
      }
      if (!status) {
        alert('Could not determine coverage status.');
        return;
      }
      // Normaliza el status para que coincida con las claves del mapeo
      const normalizedStatus = status.replace(/\s+/g, ' ').trim().toLowerCase();
      let pageMap = {
        'yes': window.ewoLocationConfig.pageCoverageYes,
        'maybe': window.ewoLocationConfig.pageCoverageMaybe,
        'not yet': window.ewoLocationConfig.pageCoverageNotYet,
        'no': window.ewoLocationConfig.pageCoverageNo
      };
      // Fallback para variantes (sin espacios)
      let dest = pageMap[normalizedStatus] || pageMap[normalizedStatus.replace(/\s/g, '')];
      if (dest) {
        window.location.href = dest;
      } else {
        alert('No destination page configured for status: ' + status);
      }
    } catch (e) {
      alert('Error fetching coverage status');
    }
  }

  // --- Replace modal input with plain text address ---
  function updateModalAddressText(address) {
    const box = document.querySelector('.ewo-map-address-box');
    if (!box) return;
    let span = document.getElementById('ewo-map-address-text');
    if (!span) {
      span = document.createElement('span');
      span.id = 'ewo-map-address-text';
      span.className = 'ewo-map-address-text';
      // Remove input if exists
      const input = document.getElementById('ewo-map-address-input');
      if (input) input.remove();
      // Insert span before geolocate button
      const geoBtn = document.getElementById('ewo-map-geolocate');
      box.insertBefore(span, geoBtn);
    }
    span.textContent = address;
  }

  // --- Unificar lógica de ambos botones Check Availability ---
  async function handleCheckAvailabilityClick(e) {
    e.preventDefault();
    let lat, lng;
    if (map && marker) {
      if (config.mapProvider === 'google' && typeof marker.getPosition === 'function') {
        const pos = marker.getPosition();
        lat = pos.lat();
        lng = pos.lng();
      } else if (typeof marker.getLatLng === 'function') {
        const pos = marker.getLatLng();
        lat = pos.lat;
        lng = pos.lng;
      }
    }
    if (!lat || !lng) {
      alert('Please select a location on the map.');
      return;
    }
    submitCoverageCheck(lat, lng);
  }

  renderStep();
})(); 