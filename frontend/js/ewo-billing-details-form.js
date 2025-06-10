// Billing Details Form JS

// Switch logic
document.addEventListener('DOMContentLoaded', function() {
  // --- Precargar datos del usuario si existen (compatibilidad con ambas claves y callback) ---
  const userDetails = JSON.parse(
    localStorage.getItem('ewo_user_details') ||
    localStorage.getItem('ewo-user-data') ||
    localStorage.getItem('ewo_request_callback_user') ||
    '{}'
  );
  // Mapeo de nombres antiguos a nuevos
  const nameMap = {
    mobile: 'mobile_number',
    referral: 'referral_code'
  };
  for (const key in userDetails) {
    if (userDetails.hasOwnProperty(key)) {
      const mappedKey = nameMap[key] || key;
      const input = document.querySelector(`[name="${mappedKey}"]`);
      if (input) input.value = userDetails[key];
    }
  }

  // --- Precargar dirección estructurada si existe ---
  const structuredAddress = JSON.parse(localStorage.getItem('ewo_structured_address') || '{}');
  if (structuredAddress) {
    if (structuredAddress.address_line_one && document.querySelector('[name="address_line_one"]')) {
      document.querySelector('[name="address_line_one"]').value = structuredAddress.address_line_one;
    }
    if (structuredAddress.city && document.querySelector('[name="city"]')) {
      document.querySelector('[name="city"]').value = structuredAddress.city;
    }
    if (structuredAddress.state && document.querySelector('[name="state"]')) {
      document.querySelector('[name="state"]').value = structuredAddress.state;
    }
    if (structuredAddress.zip && document.querySelector('[name="zip"]')) {
      document.querySelector('[name="zip"]').value = structuredAddress.zip;
    }
  }

  // Precargar toggles Yes/No (cada uno por separado)
  const toggles = [
    'text_messages_for_operational_alerts',
    'email_messages_for_operational_alerts',
    'subscribe_to_text_payments',
    'email_messages_for_wisper_news'
  ];
  toggles.forEach(name => {
    const value = userDetails[name];
    if (value) {
      const hidden = document.getElementById(name);
      if (hidden) hidden.value = value;
      document.querySelectorAll(`.ewo-toggle-btn[data-target="${name}"]`).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.value === value);
      });
    }
  });

  // --- Lógica de toggles: cada uno guarda su valor por separado ---
  document.querySelectorAll('.ewo-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = this.dataset.target;
      const value = this.dataset.value;
      // Desactivar todos los botones del grupo
      this.parentNode.querySelectorAll('.ewo-toggle-btn').forEach(b => b.classList.remove('active'));
      // Activar el botón seleccionado
      this.classList.add('active');
      // Actualizar el input hidden correspondiente
      document.getElementById(target).value = value;
    });
  });

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
      // Limpiar errores previos
      form.querySelectorAll('.ewo-field-error').forEach(el => el.classList.remove('ewo-field-error'));
      hidePopover();
      // Validar campos requeridos manualmente
      const requiredNames = [
        'first_name', 'last_name', 'email', 'mobile_number', 'ad_source',
        'address_line_one', 'city', 'state', 'zip'
      ];
      let firstInvalid = null;
      requiredNames.forEach(name => {
        const field = form.querySelector(`[name="${name}"]`);
        if (field && (!field.value || (field.tagName === 'SELECT' && field.value === ''))) {
          field.classList.add('ewo-field-error');
          if (!firstInvalid) firstInvalid = field;
        }
      });
      // Validar T&C's como toggle Yes/No
      const agreeTerms = form.querySelector('input[name="agree_terms"]');
      const agreeTermsGroup = form.querySelector('.ewo-toggle-group [data-target="agree_terms"]').parentNode;
      if (!agreeTerms || agreeTerms.value !== 'Yes') {
        agreeTermsGroup.classList.add('ewo-field-error');
        if (!firstInvalid) firstInvalid = agreeTermsGroup;
      } else {
        agreeTermsGroup.classList.remove('ewo-field-error');
      }
      if (firstInvalid) {
        firstInvalid.focus();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Solo mostrar el popover si el error es el checkbox y no hay otros errores
        const onlyCheckboxError = (firstInvalid === agreeTermsGroup) && requiredNames.every(name => {
          const field = form.querySelector(`[name="${name}"]`);
          return !field || field.value || (field.tagName === 'SELECT' && field.value !== '');
        });
        if (onlyCheckboxError) {
          showPopover(agreeTermsGroup, "You must agree to the T&C's to continue.");
        }
        return;
      }
      saveUserDetailsToLocalStorage();
      // --- Enviar a la API de oportunidad ---
      const payload = buildEwoOpportunityPayloadFromLocalStorage();
      fetch('https://dev-middleware.wisperisp.com/middleware/opportunity/createOpportunity.php', {
        method: 'POST',
        body: new URLSearchParams(payload)
      })
      .then(res => res.json())
      .then(data => {
        localStorage.setItem('ewo-opportunity-response', JSON.stringify(data));
        if (data && data.success) {
          window.location.href = '/thank-you/';
        } else {
          alert('There was a problem submitting your request. Please check your data and try again.');
        }
      })
      .catch(err => {
        alert('AJAX error: ' + err.message);
      });
    });
  }

  // --- Cálculo de totales dinámicos para el checkout ---
  const allPackages = JSON.parse(localStorage.getItem('ewo-all-packages') || '[]');
  const planId = localStorage.getItem('ewo-selected-plan-id');
  const addonsIds = JSON.parse(localStorage.getItem('ewo-selected-addons') || '[]');
  console.log('[EWO] allPackages:', allPackages);
  console.log('[EWO] planId:', planId);
  console.log('[EWO] addonsIds:', addonsIds);
  const plan = allPackages.find(p => String(p.plan_id) === String(planId));
  const addons = allPackages.filter(p => addonsIds.map(String).includes(String(p.plan_id)));

  let subtotal = 0;
  if (plan && plan.price) subtotal += parseFloat(plan.price);
  addons.forEach(addon => {
    if (addon.price) subtotal += parseFloat(addon.price);
  });
  let tax = 0.00;
  let total = subtotal + tax;

  if (!plan && !addons.length) {
    console.warn('[EWO] No plan or addons found for cart. Check localStorage values.');
  }

  if (document.getElementById('ewo-subtotal')) {
    document.getElementById('ewo-subtotal').textContent = '$' + subtotal.toFixed(2);
  }
  if (document.getElementById('ewo-tax')) {
    document.getElementById('ewo-tax').textContent = '$' + tax.toFixed(2);
  }
  if (document.getElementById('ewo-total')) {
    document.getElementById('ewo-total').textContent = '$' + total.toFixed(2);
  }

  // --- Validación y acción del botón CHECKOUT ---
  // Eliminar cualquier handler de click directo en .ewo-checkout-btn que haga redirección
  // Toda la lógica de envío/redirección está en el submit del formulario

  // Quitar el error visual del checkbox cuando el usuario lo marque y ocultar el popover
  const agreeTerms = document.getElementById('agree_terms');
  if (agreeTerms) {
    agreeTerms.addEventListener('change', function() {
      if (this.checked) {
        this.classList.remove('ewo-field-error');
        hidePopover();
      }
    });
    agreeTerms.addEventListener('focus', hidePopover);
    agreeTerms.addEventListener('click', hidePopover);
  }

  // Prellenar PIN aleatorio solo en el formulario de checkout
  var pinInput = document.querySelector('#ewo-billing-details-form #support_pin');
  if (pinInput && !pinInput.value) {
    var randomPin = String(Math.floor(1000 + Math.random() * 9000));
    pinInput.value = randomPin;
  }

  // Lógica de toggles para agree_terms
  document.querySelectorAll('.ewo-toggle-btn[data-target="agree_terms"]').forEach(btn => {
    btn.addEventListener('click', function() {
      const group = this.parentNode;
      group.querySelectorAll('.ewo-toggle-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      document.getElementById('agree_terms').value = this.dataset.value;
      group.classList.remove('ewo-field-error');
    });
  });
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
  // Agregar api-key desde la config global
  if (window.ewoLocationConfig && window.ewoLocationConfig.apiKey) {
    payload['api-key'] = window.ewoLocationConfig.apiKey;
  }
  return payload;
}

function showPopover(target, message) {
  // Elimina popovers previos
  document.querySelectorAll('.ewo-popover').forEach(p => p.remove());
  const popover = document.createElement('div');
  popover.className = 'ewo-popover';
  popover.innerHTML = message + '<div class="ewo-popover-arrow"></div>';
  // Posicionar relativo al checkbox
  const parent = target.closest('.ewo-checkbox');
  if (parent) {
    parent.appendChild(popover);
  }
}

function hidePopover() {
  document.querySelectorAll('.ewo-popover').forEach(p => p.remove());
} 