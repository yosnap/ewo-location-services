// Billing Details Form JS

// Switch logic
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.ewo-switch').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = this.dataset.target;
      const value = this.dataset.value;
      this.parentNode.querySelectorAll('.ewo-switch').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      document.getElementById(target).value = value;
    });
  });
  // Set default active
  document.querySelectorAll('.ewo-form-switch').forEach(group => {
    const yesBtn = group.querySelector('.ewo-switch[data-value="Yes"]');
    if (yesBtn) yesBtn.classList.add('active');
  });

  // Submit handler
  const form = document.getElementById('ewo-billing-details-form');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!document.getElementById('agree_terms').checked) {
        alert("You must agree to the T&C's to continue.");
        return false;
      }
      saveUserDetailsToLocalStorage();
      const payload = buildEwoOpportunityPayloadFromLocalStorage();
      payload.action = 'ewo_create_opportunity';

      fetch(window.ewoLocationConfig.ajax_url, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: new URLSearchParams(payload)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Opportunity created successfully!');
          // Limpia localStorage si quieres aquí
        } else {
          alert('Error: ' + (data.data && data.data.message ? data.data.message : 'Unknown error'));
        }
      })
      .catch(err => {
        alert('AJAX error: ' + err.message);
      });
    });
  }
});

// Guardar datos del formulario en localStorage
function saveUserDetailsToLocalStorage() {
  const form = document.getElementById('ewo-billing-details-form');
  const formData = new FormData(form);
  const userDetails = {};
  for (let [key, value] of formData.entries()) {
    if (key !== 'agree_terms') {
      userDetails[key] = value;
    }
  }
  localStorage.setItem('ewo_user_details', JSON.stringify(userDetails));
}

// Construir el payload final desde localStorage
function buildEwoOpportunityPayloadFromLocalStorage() {
  const userDetails = JSON.parse(localStorage.getItem('ewo_user_details') || '{}');
  const serviceabilityResults = JSON.parse(localStorage.getItem('ewo_serviceability_results') || 'null');
  const selectedPlan = JSON.parse(localStorage.getItem('ewo_selected_plan') || 'null');
  const selectedRecurringAddons = JSON.parse(localStorage.getItem('ewo_selected_recurring_addons') || '[]');
  const selectedVoiceAddons = JSON.parse(localStorage.getItem('ewo_selected_voice_addons') || '[]');

  const payload = { ...userDetails };
  payload.serviceability_results_json = JSON.stringify(serviceabilityResults ? [serviceabilityResults] : []);
  payload.selected_internet_plans_json = JSON.stringify(selectedPlan ? [selectedPlan] : []);
  payload.selected_recurring_addons_json = JSON.stringify(selectedRecurringAddons);
  payload.selected_voice_addons_json = JSON.stringify(selectedVoiceAddons);

  if (serviceabilityResults && serviceabilityResults.location_lat) {
    payload.latitude = serviceabilityResults.location_lat;
    payload.longitude = serviceabilityResults.location_lng;
  }
  if (serviceabilityResults && serviceabilityResults.coverage_code) {
    payload.coverage_code = serviceabilityResults.coverage_code;
  }

  return payload;
} 