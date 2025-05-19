/**
 * Lógica de planes y addons para el multistep de EWO Location Services
 * Modular, solo gestiona la obtención, renderizado y selección de planes y addons
 *
 * @since 1.0.0
 */
(function ($) {
  "use strict";

  // Variables globales para compartir con otros módulos
  window.ewoSelectedPlan = null;
  window.ewoAvailableAddons = [];

  // --- NUEVO: Estado para paginación y vista ---
  let plansData = [];
  let filteredPlans = [];
  let currentPage = 1;
  let columns = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.columns) ? parseInt(window.ewoServiceListingOptions.columns, 10) : 3;
  let perPage = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.per_page) ? parseInt(window.ewoServiceListingOptions.per_page, 10) : 4;
  let currentView = 'grid';

  /**
   * Obtiene los planes disponibles para un coverage_code
   * @param {string} coverageCode
   * @param {function} callback Opcional, se llama tras renderizar
   */
  window.ewoGetPlansForCoverage = function(coverageCode, callback) {
    const latitude = localStorage.getItem('ewo_latitude') || $('#ewo-latitude').val();
    const longitude = localStorage.getItem('ewo_longitude') || $('#ewo-longitude').val();
    localStorage.setItem('ewo_coverage_code', coverageCode);
    window.ewoShowPreloader('Loading plans...');
    showPlanSection();
    $.ajax({
      url: ewoLocationServices.ajax_url,
      type: 'POST',
      data: {
        action: 'ewo_get_packages',
        nonce: ewoLocationServices.nonce,
        coverage_code: coverageCode,
        latitude: latitude,
        longitude: longitude
      },
      success: function(response) {
        if (response.success && response.data && Array.isArray(response.data.packages) && response.data.packages.length > 0) {
          renderPlans(response.data.packages);
          window.ewoAvailableAddons = response.data.addons || [];
          if (typeof callback === 'function') callback(response.data);
        } else {
          $('#ewo-plan-list').html('<div class="ewo-error">No plans available for this location/service.</div>');
        }
        window.ewoHidePreloader();
      },
      error: function() {
        $('#ewo-plan-list').html('<div class="ewo-error">Error loading plans. Please try again.</div>');
        window.ewoHidePreloader();
      }
    });
  };

  /**
   * Renderiza la lista de planes y permite su selección
   * @param {Array} plans
   */
  function renderPlans(plans) {
    // Adaptar los datos al formato esperado por renderPlansSection
    const cards = plans.map(function(plan) {
      return {
        id: plan.id || plan.plan_id || plan.code || plan.name || plan.plan_name || Math.random().toString(36).substr(2,9),
        name: plan.plan_name || plan.name || 'Unnamed Plan',
        readable_type: plan.readable_type || plan.type || '',
        price: plan.price || plan.plan_price || '0.00',
        price_with_auto_pay_discount_applied: plan.price_with_auto_pay_discount_applied || plan.plan_price_with_auto_pay_discount_applied || '',
        features: plan.features || (plan.plan_features ? plan.plan_features.split('\n') : []) || (plan.plan_description ? [plan.plan_description] : [])
      };
    });
    // Renderizar usando la función global
    if (typeof window.renderPlansSection === 'function') {
      $('#ewo-plan-list').html('<div id="ewo-plans-container"></div>');
      window.renderPlansSection(cards);
    } else {
      // Fallback: renderizado simple
      let html = '<div class="ewo-plan-options">';
      plans.forEach(function(plan, idx) {
        html += `<div class="ewo-plan-item">
          <input type="radio" name="ewo-plan-radio" id="ewo-plan-radio-${idx}" value="${encodeURIComponent(JSON.stringify(plan))}" ${idx === 0 ? 'checked' : ''}>
          <label for="ewo-plan-radio-${idx}">
            <strong>${plan.plan_name || 'Unnamed Plan'}</strong><br>
            <span>${plan.plan_description || ''}</span><br>
            <span>Price: $${plan.price || '0.00'}</span>
          </label>
        </div>`;
      });
      html += '</div>';
      $('#ewo-plan-list').html(html);
    }
    window.ewoSelectedPlan = plans[0] || null;
    $('input[name="ewo-plan-radio"]').on('change', function() {
      const planStr = decodeURIComponent($(this).val());
      window.ewoSelectedPlan = JSON.parse(planStr);
    });
  }

  /**
   * Muestra el paso de planes (puede ser redefinido por el multistep principal)
   */
  function showPlanSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-plan-container").addClass("active");
    if (typeof setActiveStep === 'function') setActiveStep(3);
  }

  function renderPlanControls(total) {
    const $container = $('#ewo-plans-controls');
    $container.empty();
    // Fila principal de controles
    $container.append(`
      <div class="ewo-controls-row ewo-main-controls">
        <div class="ewo-controls-left">
          <span class="ewo-plans-count">${total} plans found</span>
        </div>
        <div class="ewo-controls-right">
          <div class="ewo-columns-select">Columns: <select id="ewo-plans-columns-select" class="ewo-compact-select">${[2,3,4].map(opt => `<option value="${opt}" ${columns===opt?'selected':''}>${opt}</option>`).join('')}</select></div>
          <div class="ewo-per-page-select">Items per page: <select id="ewo-plans-per-page-select" class="ewo-compact-select">${[2,4,8,12].map(opt => `<option value="${opt}" ${perPage===opt?'selected':''}>${opt}</option>`).join('')}</select></div>
          <div class="ewo-view-toggle">
            <button class="ewo-plans-toggle-btn" data-view="grid" ${currentView==='grid'?'disabled':''}>Grid</button>
            <button class="ewo-plans-toggle-btn" data-view="list" ${currentView==='list'?'disabled':''}>List</button>
          </div>
        </div>
      </div>
    `);
  }

  function renderPlansPagination(total) {
    const $container = $('#ewo-plans-pagination');
    $container.empty();
    const totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) return;
    let html = '<div class="ewo-pagination">';
    if (currentPage > 1) {
      html += `<button class="ewo-plans-page-btn" data-page="${currentPage - 1}">Previous</button>`;
    }
    for (let i = 1; i <= totalPages; i++) {
      html += `<button class="ewo-plans-page-btn${i === currentPage ? ' active' : ''}" data-page="${i}">${i}</button>`;
    }
    if (currentPage < totalPages) {
      html += `<button class="ewo-plans-page-btn" data-page="${currentPage + 1}">Next</button>`;
    }
    html += '</div>';
    $container.html(html);
  }

  function renderPlansSection(plans) {
    plansData = plans;
    filteredPlans = plans;
    currentPage = 1;
    renderPlansUI();
  }

  function renderPlansUI() {
    const $container = $('#ewo-plans-container');
    if (!$container.length) return;
    $container.empty();
    // Controles
    if ($('#ewo-plans-controls').length === 0) {
      $container.before('<div id="ewo-plans-controls"></div>');
    }
    renderPlanControls(filteredPlans.length);
    if ($('#ewo-plans-pagination').length === 0) {
      $container.after('<div id="ewo-plans-pagination"></div>');
    }
    renderPlansPagination(filteredPlans.length);
    // Paginación
    let startIdx = (currentPage - 1) * perPage;
    let endIdx = startIdx + perPage;
    const pagePlans = filteredPlans.slice(startIdx, endIdx);
    // Layout
    $container.removeClass('ewo-grid ewo-list');
    if (currentView === 'grid') {
      $container.addClass('ewo-grid');
      $container.css({
        display: 'grid',
        gridTemplateColumns: `repeat(${columns}, 1fr)`,
        gap: '1.5rem'
      });
    } else {
      $container.addClass('ewo-list');
      $container.css({ display: 'block' });
    }
    // Render cards
    let html = '';
    pagePlans.forEach(function(plan, idx) {
      const readableType = plan.readable_type || '';
      const price = plan.price ? parseFloat(plan.price) : null;
      const offer = plan.price_with_auto_pay_discount_applied ? parseFloat(plan.price_with_auto_pay_discount_applied) : null;
      const hasDiscount = offer && price && offer < price;
      const selectedClass = (idx === 0 && currentPage === 1) ? 'selected' : '';
      html += `
        <div class="ewo-plan-card ${selectedClass}" data-plan-id="${plan.id}" data-plan-json='${JSON.stringify(plan)}'>
          ${readableType ? `<div class='ewo-addon-type'>${readableType}</div>` : ''}
          <div class="ewo-addon-header">
            <span class="ewo-addon-name">${plan.name || 'Plan'}</span>
            <span class="ewo-addon-price">
              ${hasDiscount ? `<span class='ewo-plan-price-old'>$${price.toFixed(2)}</span> <span class='ewo-plan-price-offer'>$${offer.toFixed(2)}</span>` : `<span class='ewo-plan-price'>$${(offer || price || 0).toFixed(2)}</span>`}
            </span>
          </div>
          <div class="ewo-addon-description">
            <ul>
              ${(plan.features || []).map(f => `<li>${f}</li>`).join('')}
            </ul>
          </div>
        </div>
      `;
    });
    $container.html(html);
    // Selección visual por click en la card
    $(document).off('click', '.ewo-plan-card').on('click', '.ewo-plan-card', function(e) {
      $('.ewo-plan-card').removeClass('selected');
      $(this).addClass('selected');
      // Guardar selección global
      const planStr = $(this).attr('data-plan-json');
      window.ewoSelectedPlan = JSON.parse(planStr);
      localStorage.setItem('ewo_selected_plan', planStr);
    });
  }

  // Eventos de controles
  $(document).on('change', '#ewo-plans-columns-select', function() {
    columns = parseInt($(this).val(), 10);
    renderPlansUI();
  });
  $(document).on('change', '#ewo-plans-per-page-select', function() {
    perPage = parseInt($(this).val(), 10);
    currentPage = 1;
    renderPlansUI();
  });
  $(document).on('click', '.ewo-plans-toggle-btn', function() {
    currentView = $(this).data('view');
    renderPlansUI();
  });
  $(document).on('click', '.ewo-plans-page-btn', function() {
    const page = parseInt($(this).data('page'), 10);
    if (!isNaN(page)) {
      currentPage = page;
      renderPlansUI();
    }
  });
  // Exponer función global
  window.renderPlansSection = renderPlansSection;

})(jQuery); 