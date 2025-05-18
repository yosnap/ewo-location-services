(function ($) {
  "use strict";

  // Prellenar los campos de dirección y usuario al mostrar el paso de usuario
  window.showUserSection = function() {
    $(".ewo-section").removeClass("active");
    $("#ewo-user-container").addClass("active");
    if (typeof setActiveStep === 'function') setActiveStep(4);
    // 1. Si existe ewo_user_data en localStorage, usarlo para prellenar todo
    let userData = null;
    if (localStorage.getItem('ewo_user_data')) {
      try {
        userData = JSON.parse(localStorage.getItem('ewo_user_data'));
      } catch (e) { userData = null; }
    }
    if (userData) {
      $('#ewo-user-first-name').val(userData.first_name || '');
      $('#ewo-user-last-name').val(userData.last_name || '');
      $('#ewo-user-email').val(userData.email || '');
      $('#ewo-user-username').val(userData.username || '');
      $('#ewo-user-mobile').val(userData.mobile || '');
      $('#ewo-address-line-one').val(userData.address_line_one || '');
      $('#ewo-city').val(userData.city || '');
      $('#ewo-state').val(userData.state || '');
      $('#ewo-zip').val(userData.zip || '');
    } else {
      // 2. Si no, usar datos de WordPress y dirección de ewo_structured_address
      if (typeof ewoUserData !== 'undefined' && ewoUserData.logged_in) {
        $('#ewo-user-first-name').val(ewoUserData.first_name || '');
        $('#ewo-user-last-name').val(ewoUserData.last_name || '');
        $('#ewo-user-email').val(ewoUserData.email || '');
        $('#ewo-user-username').val(ewoUserData.username || '');
      }
      let addr = window.structuredAddress;
      if (!addr || !addr.address_line_one) {
        try {
          addr = JSON.parse(localStorage.getItem('ewo_structured_address')) || {};
        } catch (e) { addr = {}; }
      }
      $('#ewo-address-line-one').val(addr.address_line_one || '');
      $('#ewo-city').val(addr.city || '');
      $('#ewo-state').val(addr.state || '');
      $('#ewo-zip').val(addr.zip || '');
    }
    // Limpiar mensaje de error al mostrar el formulario
    $('#ewo-user-error').text('').hide();
  };

  // Manejar el submit del formulario de usuario
  $(document).on('submit', '#ewo-user-form', function(e) {
    e.preventDefault();
    window.ewoShowPreloader('Processing user data...');
    const data = {
      action: 'ewo_create_customer',
      nonce: ewoLocationServices.nonce,
      first_name: $('#ewo-user-first-name').val(),
      last_name: $('#ewo-user-last-name').val(),
      type: 'person',
      subtype: 'residential',
      company_name: 'Wisper ISP',
      mobile_number: $('#ewo-user-mobile').val(),
      email: $('#ewo-user-email').val(),
      address_line_one: $('#ewo-address-line-one').val() || '',
      city: $('#ewo-city').val() || '',
      state: $('#ewo-state').val() || '',
      zip: $('#ewo-zip').val() || '',
      ad_source: $('#ewo-ad-source').val() || '',
      added_by: $('#ewo-added-by').val() || '',
      support_pin: $('#ewo-support-pin').val() || '',
      status: 'new',
      address_line_two: $('#ewo-address-line-two').val() || '',
      lat: $('#ewo-latitude').val() || '',
      lng: $('#ewo-longitude').val() || '',
      text_messages_for_operational_alerts: $('#ewo-text-messages-for-operational-alerts').val() || '',
      email_messages_for_operational_alerts: $('#ewo-email-messages-for-operational-alerts').val() || '',
      email_messages_for_wisper_news: $('#ewo-email-messages-for-wisper-news').val() || '',
      subscribe_to_text_payments: $('#ewo-subscribe-to-text-payments').val() || ''
    };
    console.log('Enviando formulario usuario', data);
    // Guardar datos de usuario y dirección en localStorage
    localStorage.setItem('ewo_user_data', JSON.stringify({
      first_name: data.first_name,
      last_name: data.last_name,
      email: data.email,
      username: (typeof ewoUserData !== 'undefined' && ewoUserData.logged_in && ewoUserData.username)
        ? ewoUserData.username
        : ($('#ewo-user-username').val() || ''),
      mobile: data.mobile_number,
      address_line_one: data.address_line_one,
      city: data.city,
      state: data.state,
      zip: data.zip
    }));
    console.log('ewo_user_data guardado:', localStorage.getItem('ewo_user_data'));
    $.ajax({
      url: ewoLocationServices.ajax_url,
      type: 'POST',
      data: data,
      success: function(response) {
        window.ewoHidePreloader();
        console.log('Respuesta createCustomer:', response);
        if (response.success) {
          if (typeof showConfirmationSection === 'function') showConfirmationSection();
        } else {
          if (typeof showError === 'function') {
            showError('#ewo-user-error', response.data && response.data.message ? response.data.message : 'Error creating customer.');
          }
        }
      },
      error: function() {
        window.ewoHidePreloader();
        if (typeof showError === 'function') {
          showError('#ewo-user-error', 'Error connecting to the server. Please try again.');
        }
      }
    });
  });

  // Manejar el click del botón Continue para guardar y enviar el formulario
  $(document).on('click', '#ewo-user-continue', function(e) {
    e.preventDefault();
    // Recopilar datos igual que en el submit
    const data = {
      action: 'ewo_create_customer',
      nonce: ewoLocationServices.nonce,
      first_name: $('#ewo-user-first-name').val(),
      last_name: $('#ewo-user-last-name').val(),
      type: 'person',
      subtype: 'residential',
      company_name: 'Wisper ISP',
      mobile_number: $('#ewo-user-mobile').val(),
      email: $('#ewo-user-email').val(),
      address_line_one: $('#ewo-address-line-one').val() || '',
      city: $('#ewo-city').val() || '',
      state: $('#ewo-state').val() || '',
      zip: $('#ewo-zip').val() || '',
      ad_source: $('#ewo-ad-source').val() || '',
      added_by: $('#ewo-added-by').val() || '',
      support_pin: $('#ewo-support-pin').val() || '',
      status: 'new',
      address_line_two: $('#ewo-address-line-two').val() || '',
      lat: $('#ewo-latitude').val() || '',
      lng: $('#ewo-longitude').val() || '',
      text_messages_for_operational_alerts: $('#ewo-text-messages-for-operational-alerts').val() || '',
      email_messages_for_operational_alerts: $('#ewo-email-messages-for-operational-alerts').val() || '',
      email_messages_for_wisper_news: $('#ewo-email-messages-for-wisper-news').val() || '',
      subscribe_to_text_payments: $('#ewo-subscribe-to-text-payments').val() || ''
    };
    console.log('Enviando formulario usuario (click Continue)', data);
    // Guardar datos de usuario y dirección en localStorage
    localStorage.setItem('ewo_user_data', JSON.stringify({
      first_name: data.first_name,
      last_name: data.last_name,
      email: data.email,
      username: (typeof ewoUserData !== 'undefined' && ewoUserData.logged_in && ewoUserData.username)
        ? ewoUserData.username
        : ($('#ewo-user-username').val() || ''),
      mobile: data.mobile_number,
      address_line_one: data.address_line_one,
      city: data.city,
      state: data.state,
      zip: data.zip
    }));
    console.log('ewo_user_data guardado:', localStorage.getItem('ewo_user_data'));
    window.ewoShowPreloader('Processing user data...');
    $.ajax({
      url: ewoLocationServices.ajax_url,
      type: 'POST',
      data: data,
      success: function(response) {
        window.ewoHidePreloader();
        console.log('Respuesta createCustomer:', response);
        if (response.success) {
          if (typeof showConfirmationSection === 'function') showConfirmationSection();
        } else {
          if (typeof showError === 'function') {
            showError('#ewo-user-error', response.data && response.data.message ? response.data.message : 'Error creating customer.');
          }
        }
      },
      error: function() {
        window.ewoHidePreloader();
        if (typeof showError === 'function') {
          showError('#ewo-user-error', 'Error connecting to the server. Please try again.');
        }
      }
    });
  });

})(jQuery); 