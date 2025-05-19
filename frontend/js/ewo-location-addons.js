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
      html = '<div class="ewo-addon-options">';
      addons.forEach(function(addon, idx) {
        html += `<div class="ewo-addon-item">
          <input type="checkbox" class="ewo-addon-select" id="ewo-addon-${idx}" value="${addon.plan_id}">
          <label for="ewo-addon-${idx}">
            <strong>${addon.plan_name || 'Addon'}</strong> - $${addon.price || '0.00'}<br>
            <span>${addon.plan_description || ''}</span>
          </label>
        </div>`;
      });
      html += '</div>';
    }
    $('#ewo-addons-list').html(html);
    // Guardar addons seleccionados en localStorage cuando se seleccionan
    $(document).off('change', '.ewo-addon-select').on('change', '.ewo-addon-select', function() {
      const selectedAddons = [];
      $('.ewo-addon-select:checked').each(function() {
        selectedAddons.push($(this).val());
      });
      localStorage.setItem('ewo_selected_addons', JSON.stringify(selectedAddons));
    });
  };

  // Exponer función para limpiar addons
  window.ewoClearAddons = function() {
    $('#ewo-addons-list').empty();
    localStorage.removeItem('ewo_selected_addons');
  };

})(jQuery); 