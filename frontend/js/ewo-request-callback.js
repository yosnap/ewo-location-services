// JS to handle opening and closing the Request a Callback modal

document.addEventListener('DOMContentLoaded', function () {
  // Open modal on click (delegación sobre el div con id)
  var callbackDiv = document.getElementById('request-a-callback');
  if (callbackDiv) {
    callbackDiv.addEventListener('click', function(e) {
      if (e.target.closest('a') || e.target.closest('.elementor-button')) {
        console.log('Request a Callback button clicked');
        e.preventDefault();
        var modal = document.querySelector('.ewo-modal-overlay');
        if (modal) {
          modal.style.display = 'flex';
          document.body.classList.add('ewo-modal-open');
          // Autofocus on first input field
          var firstInput = modal.querySelector('form input, form select, form textarea');
          if (firstInput) {
            setTimeout(function() { firstInput.focus(); }, 100);
          }
        }
      }
    });
  }

  // Close modal on X button
  document.querySelectorAll('.ewo-modal-overlay .ewo-modal-close').forEach(function (el) {
    el.addEventListener('click', function () {
      var modal = el.closest('.ewo-modal-overlay');
      if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('ewo-modal-open');
      }
    });
  });

  // Close modal on click outside content
  document.querySelectorAll('.ewo-modal-overlay').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        modal.style.display = 'none';
        document.body.classList.remove('ewo-modal-open');
      }
    });
  });

  // Close modal on ESC key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      document.querySelectorAll('.ewo-modal-overlay').forEach(function (modal) {
        if (modal.style.display === 'flex') {
          modal.style.display = 'none';
          document.body.classList.remove('ewo-modal-open');
        }
      });
    }
  });
}); 