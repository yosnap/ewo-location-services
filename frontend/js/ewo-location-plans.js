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

  /**
   * Obtiene los planes disponibles para un coverage_code
   * @param {string} coverageCode
   * @param {function} callback Opcional, se llama tras renderizar
   */
  window.ewoGetPlansForCoverage = function(coverageCode, callback) {
    $('#ewo-plan-list').html('<div class="ewo-loading"><span class="ewo-spinner"></span> Loading plans...</div>');
    showPlanSection();
    $.ajax({
      url: ewoLocationServices.ajax_url,
      type: 'POST',
      data: {
        action: 'ewo_get_packages',
        nonce: ewoLocationServices.nonce,
        coverage_code: coverageCode
      },
      success: function(response) {
        console.log('Respuesta AJAX getPackages:', response);
        if (response.success && response.data && Array.isArray(response.data.packages)) {
          renderPlans(response.data.packages);
          window.ewoAvailableAddons = response.data.addons || [];
          if (typeof callback === 'function') callback(response.data);
        } else {
          $('#ewo-plan-list').html('<div class="ewo-error">Sorry, there are no plans available for the selected service or location.<br>Please try another option or contact support if you believe this is an error.</div>');
        }
      },
      error: function() {
        $('#ewo-plan-list').html('<div class="ewo-error">Error loading plans. Please try again.</div>');
      }
    });
  };

  /**
   * Renderiza la lista de planes y permite su selección
   * @param {Array} plans
   */
  function renderPlans(plans) {
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
    // Seleccionar el primer plan por defecto
    window.ewoSelectedPlan = plans[0] || null;
    // Evento para cambiar de plan
    $('input[name="ewo-plan-radio"]').on('change', function() {
      const planStr = decodeURIComponent($(this).val());
      window.ewoSelectedPlan = JSON.parse(planStr);
    });
  }

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
    $('#ewo-addon-list').html(html);
  };

  /**
   * Muestra el paso de planes (puede ser redefinido por el multistep principal)
   */
  function showPlanSection() {
    $(".ewo-section").removeClass("active");
    $("#ewo-plan-container").addClass("active");
    if (typeof setActiveStep === 'function') setActiveStep(3);
  }

  // Exponer funciones globales si se requiere desde otros módulos
  window.ewoRenderPlans = renderPlans;

})(jQuery); 