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

  // Exponer funciones globales si se requiere desde otros módulos
  window.ewoRenderPlans = renderPlans;

})(jQuery); 