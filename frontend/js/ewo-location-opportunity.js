(function ($) {
  "use strict";

  // Mostrar resumen visual de la oportunidad
  window.showOpportunitySummary = function(response) {
    if (!response || !response.data) return;
    // Mostrar y activar el contenedor de confirmación
    $('.ewo-section').removeClass('active');
    $('#ewo-confirmation-container').addClass('active').empty();
    // Guardar en localStorage y variable global
    try {
      localStorage.setItem('ewo_last_opportunity_response', JSON.stringify(response));
    } catch (e) {}
    window.ewoLastOpportunityResponse = response;

    // Leer datos seleccionados desde la respuesta de la API
    let input = response.data.api_response && response.data.api_response['input-parameters'] ? response.data.api_response['input-parameters'] : {};
    // Fallback: buscar en return-data > update_opportunity_request > opportunity_id > fields
    if ((!input.selected_internet_plans_json || !input.selected_recurring_addons_json || !input.selected_voice_addons_json) && response.data['return-data'] && response.data['return-data']['update_opportunity_request'] && response.data['return-data']['update_opportunity_request'].opportunity_id && response.data['return-data']['update_opportunity_request'].opportunity_id.fields) {
      input = response.data['return-data']['update_opportunity_request'].opportunity_id.fields;
    }
    // Plan seleccionado
    let selectedPlan = null;
    if (input.selected_internet_plans_json) {
      let plans = input.selected_internet_plans_json;
      if (typeof plans === 'string') {
        try { plans = JSON.parse(plans); } catch(e) { plans = []; }
      }
      selectedPlan = Array.isArray(plans) && plans.length > 0 ? plans[0] : null;
    }
    // Addons recurrentes
    let recurringAddons = [];
    if (input.selected_recurring_addons_json) {
      let addons = input.selected_recurring_addons_json;
      if (typeof addons === 'string') {
        try { addons = JSON.parse(addons); } catch(e) { addons = []; }
      }
      recurringAddons = Array.isArray(addons) ? addons : [];
    }
    // Addons de voz
    let voiceAddons = [];
    if (input.selected_voice_addons_json) {
      let addons = input.selected_voice_addons_json;
      if (typeof addons === 'string') {
        try { addons = JSON.parse(addons); } catch(e) { addons = []; }
      }
      voiceAddons = Array.isArray(addons) ? addons : [];
    }
    // Construir HTML del resumen
    let html = '<h2>Registration Complete</h2>';
    html += '<h3>Customer Info</h3>';
    if (input.first_name || input.last_name) {
      html += '<b>Name:</b> ' + (input.first_name || '') + ' ' + (input.last_name || '') + '<br>';
    }
    if (input.email) {
      html += '<b>Email:</b> ' + input.email + '<br>';
    }
    if (input.mobile_number) {
      html += '<b>Phone:</b> ' + input.mobile_number + '<br>';
    }
    if (input.address_line_one) {
      html += '<b>Address:</b> ' + input.address_line_one;
      if (input.city) html += ', ' + input.city;
      if (input.state) html += ', ' + input.state;
      if (input.zip) html += ' ' + input.zip;
      html += '<br>';
    }
    html += '<h3>Selected Plan</h3>';
    if (selectedPlan && (selectedPlan.plan_name || selectedPlan.name)) {
      html += '<b>' + (selectedPlan.plan_name || selectedPlan.name) + '</b> - ' + (selectedPlan.price ? selectedPlan.price + ' ' : '') + (selectedPlan.readable_type ? selectedPlan.readable_type : '') + '<br>';
      if (selectedPlan.plan_description || selectedPlan.description) html += '<small>' + (selectedPlan.plan_description || selectedPlan.description) + '</small>';
    } else {
      html += 'None<br>';
    }
    html += '<h3>Recurring Addons</h3>';
    if (recurringAddons && recurringAddons.length > 0) {
      html += '<ul>';
      recurringAddons.forEach(function(addon) {
        html += '<li>' + (addon.plan_name || addon.name || '-') + (addon.price ? ' - ' + addon.price : '') + '</li>';
      });
      html += '</ul>';
    } else {
      html += 'None<br>';
    }
    html += '<h3>Voice Addons</h3>';
    if (voiceAddons && voiceAddons.length > 0) {
      html += '<ul>';
      voiceAddons.forEach(function(addon) {
        html += '<li>' + (addon.plan_name || addon.name || '-') + (addon.price ? ' - ' + addon.price : '') + '</li>';
      });
      html += '</ul>';
    } else {
      html += 'None<br>';
    }
    html += '<h3>Opportunity Status</h3>';
    // ID y status
    let opportunityId = '-';
    let status = 'Success';
    if (response.data['return-data'] && response.data['return-data']['update_opportunity_request'] && response.data['return-data']['update_opportunity_request'].opportunity_id) {
      opportunityId = response.data['return-data']['update_opportunity_request'].opportunity_id.id || '-';
      if (response.data['return-data']['update_opportunity_request'].opportunity_id.fields && response.data['return-data']['update_opportunity_request'].opportunity_id.fields.status) {
        status = response.data['return-data']['update_opportunity_request'].opportunity_id.fields.status;
      }
    }
    html += '<b>ID:</b> ' + opportunityId + '<br>';
    html += '<b>Status:</b> ' + status + '<br>';
    // Botones de descarga e impresión
    html += '<div style="margin-top:2em;display:flex;gap:1em;">';
    html += '<button id="ewo-download-json" class="ewo-button">Download JSON</button>';
    html += '<button id="ewo-print-summary" class="ewo-button ewo-button-primary">Print</button>';
    html += '</div>';
    $('#ewo-confirmation-container').html(html);
    // Descargar JSON
    $('#ewo-download-json').off('click').on('click', function() {
      const summary = {
        api_response: response.data.api_response,
        selected_plan: selectedPlan,
        recurring_addons: recurringAddons,
        voice_addons: voiceAddons
      };
      const blob = new Blob([JSON.stringify(summary, null, 2)], {type: 'application/json'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'ewo-opportunity-summary.json';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });
    // Imprimir
    $('#ewo-print-summary').off('click').on('click', function() {
      window.print();
    });
  };
})(jQuery); 