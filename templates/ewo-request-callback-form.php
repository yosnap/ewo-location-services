<div class="ewo-modal-overlay" tabindex="-1" style="display:none;">
  <div class="ewo-modal">
    <button class="ewo-modal-close" aria-label="Close">&times;</button>
    <h2 class="ewo-modal-title">Please complete the form below and one of our agents will be in touch!</h2>
    <p class="ewo-modal-subtitle">
      The fastest way to get you on our schedule is to call our Sales Team at 800-765-7772<br>
      We're available 8:00am-7:00pm CT Monday-Friday and 9am-5pm CT Saturdays.<br>
      <br>
      If you would prefer to be contacted when a team member can best assist you, please fill out the form below.
    </p>
    <form class="ewo-user-form ewo-request-callback-form">
      <input type="hidden" name="status" value="1">
      <input type="hidden" name="customer_type" value="person">
      <input type="hidden" name="contact_source" value="9">
      <div class="ewo-user-fields-row">
        <input type="text" name="first_name" placeholder="First Name" required autocomplete="given-name">
        <input type="text" name="last_name" placeholder="Last Name" required autocomplete="family-name">
      </div>
      <div class="ewo-user-fields-row">
        <input type="email" name="email" placeholder="Email Address" required autocomplete="email">
        <input type="tel" name="mobile" placeholder="Mobile Number" required autocomplete="tel">
      </div>
      <div class="ewo-row">
        <div class="ewo-col-2 ad_source">
          <label for="ad_source">Where did you hear about Wisper?</label>
          <select id="ad_source" name="ad_source" required class="ewo-form-select ">
            <option value="">Choose Options</option>
            <option value="online_ad">Online Ad</option>
            <option value="google_bing_search">Google/Bing Search</option>
            <option value="mailer">Mailer</option>
            <option value="referral">Referral</option>
            <option value="local_event">Local Event</option>
            <option value="tv_radio_theater">TV/Radio/Theater</option>
            <option value="billboard_signage">Billboard/Signage</option>
            <option value="door_hanger_flyer">Door Hanger/Flyer</option>
            <option value="salesperson">Salesperson</option>
          </select>
        </div>
      </div>
      <div class="ewo-user-fields-row">
        <textarea name="additional_info" placeholder="Please provide any additional information here." rows="4"></textarea>
      </div>
      <button type="submit" class="ewo-btn-primary">Request Callback</button>
    </form>
    <div class="ewo-request-callback-success" style="display:none;"></div>
    <div class="ewo-request-callback-error" style="display:none; color:#e53935; text-align:center; margin-top:1.5rem; font-weight:600;"></div>
  </div>
</div>
<script>
// Save form data to localStorage and send to API
(function() {
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.ewo-request-callback-form');
    if (!form) return;
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var data = {};
      Array.from(form.elements).forEach(function(el) {
        if (el.name && (el.type !== 'submit' && el.type !== 'button')) {
          data[el.name] = el.value;
        }
      });
      // Agregar coordenadas si existen en localStorage
      try {
        var coords = JSON.parse(localStorage.getItem('ewo-location-coords'));
        if (coords && coords.lat && coords.lng) {
          data.latitude = coords.lat;
          data.longitude = coords.lng;
        }
      } catch (e) {}
      // Agregar api-key desde variable global
      if (window.ewoRequestCallbackVars && window.ewoRequestCallbackVars.apiKey) {
        data['api-key'] = window.ewoRequestCallbackVars.apiKey;
      }
      // Mapear additional_info a customer_note para el endpoint
      if (data.additional_info) {
        data.customer_note = data.additional_info;
        delete data.additional_info;
      }
      // Save to localStorage under a specific key
      localStorage.setItem('ewo_request_callback_user', JSON.stringify(data));
      // Hide previous messages
      var error = document.querySelector('.ewo-request-callback-error');
      if (error) error.style.display = 'none';
      // Send to API endpoint
      fetch('https://dev-middleware.wisperisp.com/middleware/opportunity/createOpportunity.php', {
        method: 'POST',
        headers: { },
        body: new URLSearchParams(data)
      })
      .then(function(response) { return response.json(); })
      .then(function(json) {
        if (json && json.success) {
          window.location.href = '/thank-you/';
        } else {
          if (error) {
            error.textContent = 'There was a problem submitting your request. Please try again.';
            error.style.display = 'block';
          }
        }
      })
      .catch(function() {
        if (error) {
          error.textContent = 'There was a problem submitting your request. Please try again.';
          error.style.display = 'block';
        }
      });
    });
  });
})();
</script> 