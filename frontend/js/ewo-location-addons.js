(function ($) {
  "use strict";

  // Variable global para los addons disponibles
  window.ewoAvailableAddons = window.ewoAvailableAddons || [];

  /**
   * Renderiza los addons disponibles en el paso correspondiente
   * @param {Array} addons
   */
  window.ewoRenderAddons = function(addons) {
    let html = '';
    if (!addons || !addons.length) {
      html = '<div class="ewo-no-addons">No addons available for this plan.</div>';
    } else {
      html = '<div class="ewo-addon-cards-container">';
      addons.forEach(function(addon, idx) {
        const readableType = addon.readable_type || addon.type || '';
        html += `<div class="ewo-addon-card" data-addon-id="${addon.plan_id}">
          ${readableType ? `<div class='ewo-addon-type'>${readableType}</div>` : ''}
          <div class="ewo-addon-header">
            <span class="ewo-addon-name">${addon.plan_name || 'Addon'}</span>
            <span class="ewo-addon-price">$${addon.price || '0.00'}</span>
          </div>
          <div class="ewo-addon-description">${addon.plan_description || ''}</div>
          <input type="checkbox" class="ewo-addon-select" id="ewo-addon-${idx}" value="${addon.plan_id}">
        </div>`;
      });
      html += '</div>';
    }
    $('#ewo-addons-list').html(html);
    // Sincronizar selección visual
    $('.ewo-addon-select').each(function() {
      if ($(this).is(':checked')) {
        $(this).closest('.ewo-addon-card').addClass('selected');
      }
    });
    // Evento para selección visual
    $(document).off('change', '.ewo-addon-select').on('change', '.ewo-addon-select', function() {
      if ($(this).is(':checked')) {
        $(this).closest('.ewo-addon-card').addClass('selected');
      } else {
        $(this).closest('.ewo-addon-card').removeClass('selected');
      }
      // Guardar addons seleccionados en localStorage
      const selectedAddons = [];
      $('.ewo-addon-select:checked').each(function() {
        selectedAddons.push($(this).val());
      });
      localStorage.setItem('ewo_selected_addons', JSON.stringify(selectedAddons));
    });
    // Permitir seleccionar la card entera
    $(document).off('click', '.ewo-addon-card').on('click', '.ewo-addon-card', function(e) {
      if (!$(e.target).is('input[type="checkbox"]')) {
        const $checkbox = $(this).find('input[type="checkbox"]');
        $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
      }
    });
  };

  // Exponer función para limpiar addons
  window.ewoClearAddons = function() {
    $('#ewo-addons-list').empty();
    localStorage.removeItem('ewo_selected_addons');
  };

})(jQuery); 