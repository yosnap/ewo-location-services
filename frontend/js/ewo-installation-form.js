// JS for EWO Installation Form: toggles, validación y guardado en localStorage

document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('.ewo-installation-form');
  if (!form) return;

  // Toggle handler (Rent/Own, Schedule Type, Time options)
  form.addEventListener('click', function (e) {
    var btn = e.target.closest('.ewo-toggle-btn');
    if (!btn) return;
    var target = btn.getAttribute('data-target');
    var value = btn.getAttribute('data-value');
    if (!target || !value) return;

    // Desactivar todos los toggles del grupo
    var group = form.querySelectorAll('.ewo-toggle-btn[data-target="' + target + '"]');
    group.forEach(function (el) { el.classList.remove('active'); });
    btn.classList.add('active');
    // Actualizar el input hidden
    var hidden = form.querySelector('input[name="' + target + '"]');
    if (hidden) hidden.value = value;
  });

  // Quitar error visual al corregir
  form.addEventListener('input', function(e) {
    if (e.target.classList.contains('ewo-field-error')) {
      e.target.classList.remove('ewo-field-error');
    }
  });
  form.addEventListener('change', function(e) {
    if (e.target.classList.contains('ewo-field-error')) {
      e.target.classList.remove('ewo-field-error');
    }
  });

  // Validación y guardado
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var data = {};
    var firstInvalid = null;
    Array.from(form.elements).forEach(function(el) {
      if (el.name && (el.type !== 'submit' && el.type !== 'button')) {
        data[el.name] = el.value;
        // Validar requeridos
        if (el.required && !el.value) {
          el.classList.add('ewo-field-error');
          if (!firstInvalid) firstInvalid = el;
        }
      }
    });
    if (firstInvalid) {
      firstInvalid.focus();
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    localStorage.setItem('ewo_installation_details', JSON.stringify(data));
    window.location.href = '/checkout/';
  });
}); 