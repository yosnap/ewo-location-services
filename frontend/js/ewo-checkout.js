// Este archivo ha sido renombrado a ewo-checkout.js.bak para evitar su ejecución accidental. Usar solo como referencia.

// EWO Checkout JS
(function() {
  const root = document.getElementById('ewo-checkout-root');
  if (!root) return;

  // Datos de usuario previos
  const userData = JSON.parse(localStorage.getItem('ewo-user-data') || '{}');
  // Carrito
  const planId = localStorage.getItem('ewo-selected-plan-id');
  const addonsIds = JSON.parse(localStorage.getItem('ewo-selected-addons') || '[]');
  const allPackages = JSON.parse(localStorage.getItem('ewo-all-packages') || '[]');
  const plan = allPackages.find(p => String(p.plan_id) === String(planId));
  const addons = allPackages.filter(p => addonsIds.map(String).includes(String(p.plan_id)));

  // Serviceability y coverage_code
  const serviceability = JSON.parse(localStorage.getItem('ewo-serviceability') || '{}');
  const coverage_code = localStorage.getItem('ewo-coverage-code') || '';
  const lat = localStorage.getItem('ewo-latitude') || '';
  const lng = localStorage.getItem('ewo-longitude') || '';

  // Helper para toggles
  function renderToggle(name, label, value) {
    return `
      <div class="ewo-toggle-flex-row">
        <label class="ewo-toggle-label">${label}</label>
        <div class="ewo-toggle-group">
          <button type="button" class="ewo-toggle-btn${value === 'Yes' ? ' active' : ''}" data-target="${name}" data-value="Yes">Yes</button>
          <button type="button" class="ewo-toggle-btn${value === 'No' ? ' active' : ''}" data-target="${name}" data-value="No">No</button>
        </div>
        <input type="hidden" name="${name}" value="${value || 'No'}">
      </div>
    `;
  }

  // Renderizar formulario
  let html = '';
  html += `<form class="ewo-checkout-form" autocomplete="on">
    <div class="ewo-checkout-error" style="display:none;"></div>
    <div class="ewo-checkout-fields-row">
      <div><label>First Name<span class="ewo-required">*</span><input type="text" name="first_name" required value="${userData.first_name || ''}" autocomplete="given-name"></label></div>
      <div><label>Last Name<span class="ewo-required">*</span><input type="text" name="last_name" required value="${userData.last_name || ''}" autocomplete="family-name"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      <div><label>Email Address<span class="ewo-required">*</span><input type="email" name="email" required value="${userData.email || ''}" autocomplete="email"></label></div>
      <div><label>Mobile Number<span class="ewo-required">*</span><input type="tel" name="mobile_number" required value="${userData.mobile_number || ''}" autocomplete="tel"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      <div><label>Address Line 1<span class="ewo-required">*</span><input type="text" name="address_line_one" required value="${userData.address_line_one || ''}" autocomplete="address-line1"></label></div>
      <div><label>Address Line 2<input type="text" name="address_line_two" value="${userData.address_line_two || ''}" autocomplete="address-line2"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      <div><label>City<span class="ewo-required">*</span><input type="text" name="city" required value="${userData.city || ''}" autocomplete="address-level2"></label></div>
      <div><label>State<span class="ewo-required">*</span><input type="text" name="state" required value="${userData.state || ''}" autocomplete="address-level1"></label></div>
      <div><label>ZIP Code<span class="ewo-required">*</span><input type="text" name="zip" required value="${userData.zip || ''}" autocomplete="postal-code"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      <div><label>Support Pin<span class="ewo-required">*</span><input type="text" name="support_pin" required value="${userData.support_pin || ''}"></label></div>
      <div><label>Referral Code<input type="text" name="referral_code" value="${userData.referral_code || ''}"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      <div><label>How did you hear about us?<span class="ewo-required">*</span>
        <select name="ad_source" required>
          <option value="">Choose Option</option>
          <option value="friend"${userData.ad_source === 'friend' ? ' selected' : ''}>Friend/Family</option>
          <option value="social"${userData.ad_source === 'social' ? ' selected' : ''}>Social Media</option>
          <option value="event"${userData.ad_source === 'event' ? ' selected' : ''}>Local Event</option>
          <option value="web"${userData.ad_source === 'web' ? ' selected' : ''}>Web Search</option>
          <option value="other"${userData.ad_source === 'other' ? ' selected' : ''}>Other</option>
        </select>
      </label></div>
      <div><label>Company Name<input type="text" name="company_name" value="${userData.company_name || ''}"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      <div><label>Added By<input type="text" name="added_by" value="${userData.added_by || ''}"></label></div>
      <div><label>Status<input type="text" name="status" value="${userData.status || 'new'}"></label></div>
    </div>
    <div class="ewo-checkout-fields-row">
      ${renderToggle('text_messages_for_operational_alerts', 'Text Messages for Operational Alerts', userData.text_messages_for_operational_alerts || 'Yes')}
      ${renderToggle('email_messages_for_operational_alerts', 'Email Messages for Operational Alerts', userData.email_messages_for_operational_alerts || 'Yes')}
    </div>
    <div class="ewo-checkout-fields-row">
      ${renderToggle('subscribe_to_text_payments', "Subscribe to 'Text Payments'", userData.subscribe_to_text_payments || 'Yes')}
      ${renderToggle('email_messages_for_wisper_news', 'Email Messages for Wisper News', userData.email_messages_for_wisper_news || 'Yes')}
    </div>
    <div class="ewo-checkout-fields-row">
      <div class="ewo-checkbox">
        <input type="checkbox" name="agree_terms" id="ewo-agree-terms" required>
        <label for="ewo-agree-terms">I agree to the T&C's</label>
      </div>
    </div>
    <button type="submit" class="ewo-checkout-btn">Checkout</button>
  </form>`;

  // Resumen del carrito
  let subtotal = 0;
  if (plan) subtotal += parseFloat(plan.price);
  addons.forEach(addon => { subtotal += parseFloat(addon.price); });
  let tax = 0.00;
  let total = subtotal + tax;
  html += `<div class="ewo-checkout-summary">
    <div class="ewo-cart-totals-title">Basket Total</div>
    <table class="ewo-cart-totals-table"><tbody>
      <tr><td>Subtotal</td><td class="ewo-cart-totals-value">$${subtotal.toFixed(2)}</td></tr>
      <tr><td>TAX</td><td class="ewo-cart-totals-value">$${tax.toFixed(2)}</td></tr>
      <tr><td>Total</td><td class="ewo-cart-totals-value">$${total.toFixed(2)}</td></tr>
    </tbody></table>
  </div>`;

  root.innerHTML = html;

  // Toggle logic
  root.querySelectorAll('.ewo-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const group = btn.closest('.ewo-toggle-group');
      group.querySelectorAll('.ewo-toggle-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const hidden = group.parentElement.querySelector('input[type="hidden"]');
      hidden.value = btn.dataset.value;
    });
  });

  // Handler de submit
  const form = root.querySelector('.ewo-checkout-form');
  const errorDiv = root.querySelector('.ewo-checkout-error');
  form.onsubmit = async function(e) {
    e.preventDefault();
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    // Validar campos requeridos
    const data = {};
    let valid = true;
    form.querySelectorAll('input, select').forEach(input => {
      if (input.required && !input.value) {
        input.style.borderColor = 'var(--ewo-alert)';
        valid = false;
      } else {
        input.style.borderColor = '#b5d2f7';
      }
      data[input.name] = input.value;
    });
    if (!valid) {
      errorDiv.textContent = 'Please fill in all required fields.';
      errorDiv.style.display = 'block';
      return;
    }
    // Guardar datos editados en localStorage
    localStorage.setItem('ewo-user-data', JSON.stringify(data));

    // Construir payload para createCustomer
    const customerPayload = {
      'api-key': window.ewoLocationConfig && window.ewoLocationConfig.apiKey,
      'first_name': data.first_name,
      'last_name': data.last_name,
      'type': 'person',
      'subtype': 'residential',
      'company_name': data.company_name || '',
      'mobile_number': data.mobile_number,
      'email': data.email,
      'address_line_one': data.address_line_one,
      'address_line_two': data.address_line_two || '',
      'city': data.city,
      'state': data.state,
      'zip': data.zip,
      'ad_source': data.ad_source || '',
      'added_by': data.added_by || '',
      'support_pin': data.support_pin,
      'status': data.status || 'new',
      'lat': lat,
      'lng': lng,
      'text_messages_for_operational_alerts': data.text_messages_for_operational_alerts,
      'email_messages_for_operational_alerts': data.email_messages_for_operational_alerts,
      'email_messages_for_wisper_news': data.email_messages_for_wisper_news,
      'subscribe_to_text_payments': data.subscribe_to_text_payments
    };

    // Construir payload para createOpportunity
    const opportunityPayload = {
      'api-key': window.ewoLocationConfig && window.ewoLocationConfig.apiKey,
      'address_line_one': data.address_line_one,
      'address_line_two': data.address_line_two || '',
      'city': data.city,
      'state': data.state,
      'zip': data.zip,
      'latitude': lat,
      'longitude': lng,
      'first_name': data.first_name,
      'last_name': data.last_name,
      'email': data.email,
      'mobile_number': data.mobile_number,
      'ad_source': data.ad_source || '',
      'customer_type': 'person',
      'customer_subtype': 'residential',
      'support_pin': data.support_pin,
      'status': data.status || 'new',
      'text_messages_for_operational_alerts': data.text_messages_for_operational_alerts,
      'email_messages_for_operational_alerts': data.email_messages_for_operational_alerts,
      'email_messages_for_wisper_news': data.email_messages_for_wisper_news,
      'subscribe_to_text_payments': data.subscribe_to_text_payments,
      // JSON fields
      'serviceability_results_json': JSON.stringify([serviceability]),
      'selected_internet_plans_json': JSON.stringify(plan ? [plan] : []),
      'selected_recurring_addons_json': JSON.stringify(addons.filter(a => a.type === 'recurring_service')),
      'selected_voice_addons_json': JSON.stringify(addons.filter(a => a.type === 'voice_service')),
      'tags_json': '[]'
    };

    // Enviar a createCustomer
    try {
      const res1 = await fetch(window.ewoLocationConfig.createCustomerUrl, {
        method: 'POST',
        body: new URLSearchParams(customerPayload)
      });
      const result1 = await res1.json();
      if (!result1.success) {
        errorDiv.textContent = 'Error creating customer.';
        errorDiv.style.display = 'block';
        return;
      }
      // Enviar a createOpportunity
      const res2 = await fetch(window.ewoLocationConfig.createOpportunityUrl, {
        method: 'POST',
        body: new URLSearchParams(opportunityPayload)
      });
      const result2 = await res2.json();
      if (!result2.success) {
        errorDiv.textContent = 'Error creating opportunity.';
        errorDiv.style.display = 'block';
        return;
      }
      window.location.href = '/thank-you/';
    } catch (err) {
      errorDiv.textContent = 'There was an error connecting to the server.';
      errorDiv.style.display = 'block';
    }
  };
})(); 