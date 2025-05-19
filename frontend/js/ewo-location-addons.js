(function ($) {
  "use strict";

  // Variable global para los addons disponibles
  window.ewoAvailableAddons = window.ewoAvailableAddons || [];

  // --- NUEVO: Estado para paginación y vista ---
  let addonsData = [];
  let filteredAddons = [];
  let currentPage = 1;
  let columns = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.columns) ? parseInt(window.ewoServiceListingOptions.columns, 10) : 3;
  let perPage = (window.ewoServiceListingOptions && window.ewoServiceListingOptions.per_page) ? parseInt(window.ewoServiceListingOptions.per_page, 10) : 4;
  let currentView = 'grid';

  function renderAddonControls(total) {
    const $container = $('#ewo-addons-controls');
    $container.empty();
    // Fila principal de controles
    $container.append(`
      <div class="ewo-controls-row ewo-main-controls">
        <div class="ewo-controls-left">
          <span class="ewo-addons-count">${total} addons found</span>
        </div>
        <div class="ewo-controls-right">
          <div class="ewo-columns-select">Columns: <select id="ewo-addons-columns-select" class="ewo-compact-select">${[2,3,4].map(opt => `<option value="${opt}" ${columns===opt?'selected':''}>${opt}</option>`).join('')}</select></div>
          <div class="ewo-per-page-select">Items per page: <select id="ewo-addons-per-page-select" class="ewo-compact-select">${[2,4,8,12].map(opt => `<option value="${opt}" ${perPage===opt?'selected':''}>${opt}</option>`).join('')}</select></div>
          <div class="ewo-view-toggle">
            <button class="ewo-addons-toggle-btn" data-view="grid" ${currentView==='grid'?'disabled':''}>Grid</button>
            <button class="ewo-addons-toggle-btn" data-view="list" ${currentView==='list'?'disabled':''}>List</button>
          </div>
        </div>
      </div>
    `);
  }

  function renderAddonsPagination(total) {
    const $container = $('#ewo-addons-pagination');
    $container.empty();
    const totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) return;
    let html = '<div class="ewo-pagination">';
    if (currentPage > 1) {
      html += `<button class="ewo-addons-page-btn" data-page="${currentPage - 1}">Previous</button>`;
    }
    for (let i = 1; i <= totalPages; i++) {
      html += `<button class="ewo-addons-page-btn${i === currentPage ? ' active' : ''}" data-page="${i}">${i}</button>`;
    }
    if (currentPage < totalPages) {
      html += `<button class="ewo-addons-page-btn" data-page="${currentPage + 1}">Next</button>`;
    }
    html += '</div>';
    $container.html(html);
  }

  /**
   * Renderiza los addons disponibles en el paso correspondiente
   * @param {Array} addons
   */
  window.ewoRenderAddons = function(addons) {
    addonsData = addons;
    filteredAddons = addons;
    currentPage = 1;
    renderAddonsUI();
  };

  function renderAddonsUI() {
    const $container = $('#ewo-addons-list');
    if (!$container.length) return;
    $container.empty();
    // Controles
    if ($('#ewo-addons-controls').length === 0) {
      $container.before('<div id="ewo-addons-controls"></div>');
    }
    renderAddonControls(filteredAddons.length);
    if ($('#ewo-addons-pagination').length === 0) {
      $container.after('<div id="ewo-addons-pagination"></div>');
    }
    renderAddonsPagination(filteredAddons.length);
    // Paginación
    let startIdx = (currentPage - 1) * perPage;
    let endIdx = startIdx + perPage;
    const pageAddons = filteredAddons.slice(startIdx, endIdx);
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
    pageAddons.forEach(function(addon, idx) {
      const readableType = addon.readable_type || addon.type || '';
      const selectedClass = $('.ewo-addon-select:checked').map(function(){return $(this).val();}).get().includes(String(addon.plan_id)) ? 'selected' : '';
      html += `<div class="ewo-addon-card ${selectedClass}" data-addon-id="${addon.plan_id}" data-addon-json='${JSON.stringify(addon)}'>
        ${readableType ? `<div class='ewo-addon-type'>${readableType}</div>` : ''}
        <div class="ewo-addon-header">
          <span class="ewo-addon-name">${addon.plan_name || 'Addon'}</span>
          <span class="ewo-addon-price">$${addon.price || '0.00'}</span>
        </div>
        <div class="ewo-addon-description">${addon.plan_description || ''}</div>
      </div>`;
    });
    $container.html(html);
    // Selección visual por click en la card
    $(document).off('click', '.ewo-addon-card').on('click', '.ewo-addon-card', function(e) {
      $(this).toggleClass('selected');
      // Guardar addons seleccionados en localStorage
      const selectedAddons = [];
      $('.ewo-addon-card.selected').each(function() {
        selectedAddons.push($(this).data('addon-id'));
      });
      localStorage.setItem('ewo_selected_addons', JSON.stringify(selectedAddons));
    });
  }

  // Eventos de controles
  $(document).on('change', '#ewo-addons-columns-select', function() {
    columns = parseInt($(this).val(), 10);
    renderAddonsUI();
  });
  $(document).on('change', '#ewo-addons-per-page-select', function() {
    perPage = parseInt($(this).val(), 10);
    currentPage = 1;
    renderAddonsUI();
  });
  $(document).on('click', '.ewo-addons-toggle-btn', function() {
    currentView = $(this).data('view');
    renderAddonsUI();
  });
  $(document).on('click', '.ewo-addons-page-btn', function() {
    const page = parseInt($(this).data('page'), 10);
    if (!isNaN(page)) {
      currentPage = page;
      renderAddonsUI();
    }
  });

  // Exponer función para limpiar addons
  window.ewoClearAddons = function() {
    $('#ewo-addons-list').empty();
    localStorage.removeItem('ewo_selected_addons');
  };

})(jQuery); 