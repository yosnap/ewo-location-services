(function ($) {
  "use strict";

  // Estado y variables para servicios
  let servicesData = [];
  let filteredServices = [];
  let currentPage = 1;
  let columns = 3;
  let perPage = 8;
  let currentView = 'grid';
  let searchQuery = '';
  let networkTypeFilter = '';
  let ordering = 'default';
  let isLoading = false;

  // Renderiza los servicios recibidos desde la API
  function displayServices(data) {
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
      services = [];
    }
    if (services.length === 0) {
      const noServicesMsg = `
        <div class="ewo-no-services-message" style="padding:2rem;text-align:center;color:#c2185b;font-size:1.2rem;background:#fff3f7;border-radius:8px;max-width:600px;margin:2rem auto;">
          <div style="font-size:2.2rem;margin-bottom:0.5rem;">😕</div>
          <div style="margin-bottom:1rem;">Sorry, we couldn't find any services available for the selected location.</div>
          <span style="color:#555;font-size:1rem;">Please try another address or try again later.</span>
          <div style="margin-top:2rem;">
            <button id="ewo-no-services-change-location" class="ewo-button ewo-button-secondary" style="font-size:1rem;padding:0.7em 2em;">Change location</button>
          </div>
        </div>
      `;
      $("#ewo-services-list").html(noServicesMsg);
      $("#ewo-services-pagination").empty();
      $(document).off('click', '#ewo-no-services-change-location').on('click', '#ewo-no-services-change-location', function() {
        $('#ewo-address-input').val('');
        $('#ewo-latitude').val('');
        $('#ewo-longitude').val('');
        if (typeof showLocationSection === 'function') showLocationSection();
      });
      return;
    }
    servicesData = services;
    filteredServices = services;
    currentPage = 1;
    applyFiltersAndOrdering();
    window.showServicesSection();
  }

  // Filtros, orden y renderizado de servicios
  function applyFiltersAndOrdering() {
    setPreloader(true);
    setTimeout(() => {
      let result = [...servicesData];
      if (searchQuery) {
        const q = searchQuery.toLowerCase();
        result = result.filter(s =>
          (s.coverage_code && s.coverage_code.toLowerCase().includes(q)) ||
          (s.network_type && s.network_type.toLowerCase().includes(q)) ||
          (s.coverage_confidence && s.coverage_confidence.status_text_full && s.coverage_confidence.status_text_full.toLowerCase().includes(q))
        );
      }
      if (networkTypeFilter) {
        result = result.filter(s => s.network_type === networkTypeFilter);
      }
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
    }, 200);
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
    // Aquí puedes agregar renderServiceControls, renderPagination, etc. según tu lógica modular
    // Render items (ejemplo básico):
    filteredServices.forEach(function (service) {
      $servicesList.append('<div class="ewo-service-item">' + (service.coverage_code || 'Unnamed') + '</div>');
    });
  }

  // Selección de servicio
  $(document).on("click", ".ewo-select-service", function() {
    const serviceId = $(this).data("service-id");
    const $serviceItem = $(this).closest(".ewo-service-item");
    const serviceDataStr = $serviceItem.attr("data-service-json");
    try {
      const selectedService = JSON.parse(serviceDataStr);
      $("#ewo-selected-service").val(serviceId);
      if (selectedService.coverage_code && typeof window.ewoGetPlansForCoverage === 'function') {
        localStorage.setItem('ewo_coverage_code', selectedService.coverage_code);
        window.ewoGetPlansForCoverage(selectedService.coverage_code);
      } else {
        alert('No coverage_code found for this service.');
      }
    } catch (e) {
      alert("Error al procesar los datos del servicio");
    }
  });

  // Exponer función global para mostrar servicios desde otros módulos
  window.ewoDisplayServices = displayServices;

})(jQuery); 