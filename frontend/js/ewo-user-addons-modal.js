// Modal for user details and addons selection
export function openUserAddonsModal(plan, allAddons) {
  // Depuración: mostrar el array completo que llega al modal
  console.log('allAddons en modal:', allAddons);
  // Filtro ampliado para addons
  let addons = allAddons.filter(a =>
    a.display_as_addon === true ||
    a.display_as_addon === "true" ||
    a.display_as_addon === 1 ||
    a.display_as_addon === "1"
  );
  console.log('addons filtrados:', addons);
  // Overlay
  let overlay = document.createElement('div');
  overlay.className = 'ewo-modal-overlay';
  overlay.tabIndex = -1;
  // Modal
  let modal = document.createElement('div');
  modal.className = 'ewo-modal';
  // Botón cerrar
  let closeBtn = document.createElement('button');
  closeBtn.className = 'ewo-modal-close';
  closeBtn.innerHTML = '&times;';
  closeBtn.title = 'Close';
  // Título
  let title = document.createElement('h2');
  title.className = 'ewo-modal-title';
  title.textContent = 'Fill in Your Details Below';
  // Formulario usuario
  let form = document.createElement('form');
  form.className = 'ewo-user-form';
  form.innerHTML = `
    <div class="ewo-user-fields-row">
      <input type="text" name="first_name" placeholder="First Name" required autocomplete="given-name">
      <input type="text" name="last_name" placeholder="Last Name" required autocomplete="family-name">
    </div>
    <div class="ewo-user-fields-row">
      <input type="email" name="email" placeholder="Email Address" required autocomplete="email">
      <input type="tel" name="mobile" placeholder="Mobile Number" required autocomplete="tel">
    </div>
    <div class="ewo-user-fields-row ewo-user-fields-row-select">
      <label for="ewo-referral-select" class="ewo-referral-label">Where did you hear about Wisper?</label>
      <select id="ewo-referral-select" name="ad_source" required>
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
  `;
  // Prellenar PIN aleatorio
  const pinInput = form.querySelector('#support_pin');
  if (pinInput) {
    const randomPin = String(Math.floor(1000 + Math.random() * 9000));
    pinInput.value = randomPin;
  }
  // Addons slider
  let addonsTitle = document.createElement('h3');
  addonsTitle.className = 'ewo-addons-title';
  addonsTitle.textContent = 'Add On';
  let addonsSliderWrapper = document.createElement('div');
  addonsSliderWrapper.className = 'ewo-addons-slider-wrapper';
  let addonsSlider = document.createElement('div');
  addonsSlider.className = 'ewo-addons-slider';
  addons.forEach(addon => {
    let card = document.createElement('div');
    card.className = 'ewo-addon-card';
    card.innerHTML = `
      <div class="ewo-addon-info">
        <div class="ewo-addon-name">${addon.plan_name}</div>
        <div class="ewo-addon-price">$${addon.price || 'xx.xx'}</div>
        <div class="ewo-addon-description">${addon.plan_description || ''}</div>
        <button class="ewo-addon-btn" data-addon-id="${addon.plan_id}">ADD TO CART</button>
      </div>
    `;
    addonsSlider.appendChild(card);
  });
  // Flechas del slider
  let leftArrow = document.createElement('button');
  leftArrow.className = 'ewo-addons-arrow left';
  leftArrow.innerHTML = '&#8592;';
  let rightArrow = document.createElement('button');
  rightArrow.className = 'ewo-addons-arrow right';
  rightArrow.innerHTML = '&#8594;';
  addonsSliderWrapper.appendChild(leftArrow);
  addonsSliderWrapper.appendChild(addonsSlider);
  addonsSliderWrapper.appendChild(rightArrow);
  // Botón Review Cart
  let reviewBtn = document.createElement('button');
  reviewBtn.type = 'button';
  reviewBtn.className = 'ewo-review-cart-btn';
  reviewBtn.textContent = 'REVIEW CART';
  // Estructura modal
  modal.appendChild(closeBtn);
  modal.appendChild(title);
  modal.appendChild(form);
  if (addons.length) {
    modal.appendChild(addonsTitle);
    modal.appendChild(addonsSliderWrapper);
  }
  modal.appendChild(reviewBtn);
  overlay.appendChild(modal);
  document.body.appendChild(overlay);
  document.body.classList.add('ewo-modal-open');

  // --- CSS del modal y slider ---
  const style = document.createElement('style');
  style.innerHTML = `
    .ewo-modal-overlay {
      position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
      background: rgba(20,30,50,0.7); z-index: 9999;
      display: flex; align-items: center; justify-content: center;
      animation: ewoFadeIn 0.2s;
    }
    @keyframes ewoFadeIn { from { opacity: 0; } to { opacity: 1; } }
    .ewo-modal {
      background: #eaf4ff;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(32,63,154,0.18);
      padding: 2.5rem 2.5rem 2rem 2.5rem;
      min-width: 340px; max-width: 800px; width: 98vw;
      position: relative;
      display: flex; flex-direction: column; align-items: center;
      animation: ewoPopIn 0.2s;
    }
    @keyframes ewoPopIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .ewo-modal-close {
      position: absolute; top: 18px; right: 22px; background: none; border: none; font-size: 2rem; color: #203F9A; cursor: pointer; z-index: 2;
    }
    .ewo-modal-title {
      color: var(--ewo-primary); font-size: 2rem; font-weight: 900; margin-bottom: 1.5rem; text-align: center;
    }
    .ewo-user-form { width: 100%; margin-bottom: 1.5rem; }
    .ewo-user-fields-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
    .ewo-user-fields-row-select { flex-direction: column; align-items: flex-start; gap: 0.3rem; }
    .ewo-referral-label { color: var(--ewo-primary); font-size: 1rem; font-weight: 700; margin-bottom: 0.2rem; }
    .ewo-user-fields-row input, .ewo-user-fields-row select {
      flex: 1; padding: 0.8em 1.2em; border-radius: 24px; border: 1.5px solid #b5d2f7; font-size: 1rem; background: #fff; color: #203F9A; font-weight: 500;
    }
    .ewo-user-fields-row input:focus, .ewo-user-fields-row select:focus { outline: 2px solid var(--ewo-primary); }
    .ewo-addons-title { color: var(--ewo-primary); font-size: 2rem; font-weight: 900; margin: 1.5rem 0 1rem 0; text-align: center; }
    .ewo-addons-slider-wrapper {
      width: 100%; display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 1.5rem;
    }
    .ewo-addons-slider {
      display: flex; gap: 1.5rem; overflow-x: auto; scroll-behavior: smooth;
      width: 100%; max-width: 600px; padding: 0.5rem 0;
    }
    .ewo-addon-card {
      background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(32,63,154,0.10);
      display: flex; align-items: center; min-width: 280px; max-width: 320px; padding: 1rem 1.2rem; gap: 1rem;
      flex: 0 0 48%;
    }
    .ewo-addon-img img { width: 48px; height: 48px; object-fit: contain; border-radius: 8px; background: #f5faff; }
    .ewo-addon-info { flex: 1; display: flex; flex-direction: column; align-items: flex-start; }
    .ewo-addon-name { color: var(--ewo-primary); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.2rem; }
    .ewo-addon-price { color: var(--ewo-primary); font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem; }
    .ewo-addon-btn {
      background: var(--ewo-secondary); color: var(--ewo-primary); border: 2px solid var(--ewo-primary); border-radius: 20px; padding: 0.4em 1.2em; font-weight: bold; font-size: 1rem; cursor: pointer; transition: background 0.2s, color 0.2s;
    }
    .ewo-addon-btn.selected, .ewo-addon-btn:active {
      background: var(--ewo-primary); color: #fff;
    }
    .ewo-addon-btn.remove {
      background: var(--ewo-alert); color: #fff; border-color: var(--ewo-alert); font-weight: bold;
    }
    .ewo-addons-arrow {
      position: relative; z-index: 2; background: var(--ewo-secondary); color: var(--ewo-primary); border: 2px solid var(--ewo-primary); border-radius: 50%; width: 38px; height: 38px; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; margin: 0 0.5rem; cursor: pointer; transition: background 0.2s, color 0.2s;
    }
    .ewo-addons-arrow:hover { background: var(--ewo-primary); color: #fff; }
    .ewo-addons-arrow.left { left: 0; }
    .ewo-addons-arrow.right { right: 0; }
    .ewo-review-cart-btn {
      margin-top: 1.5rem; width: 100%; background: var(--ewo-secondary); color: var(--ewo-primary); border: none; border-radius: 24px; padding: 0.9em 0; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background 0.2s, color 0.2s;
    }
    .ewo-review-cart-btn:active, .ewo-review-cart-btn:focus { background: var(--ewo-primary); color: #fff; }
    @media (max-width: 900px) {
      .ewo-modal { max-width: 98vw; }
      .ewo-addons-slider { max-width: 98vw; }
      .ewo-addon-card { min-width: 90vw; max-width: 95vw; flex-basis: 95vw; }
    }
    @media (max-width: 600px) {
      .ewo-modal { padding: 1.2rem 0.5rem 1rem 0.5rem; min-width: 0; }
      .ewo-addons-slider { gap: 0.7rem; }
      .ewo-addon-card { min-width: 90vw; max-width: 95vw; flex-basis: 95vw; }
    }
    body.ewo-modal-open { overflow: hidden !important; }
    .ewo-addon-btn.added {
      background: var(--ewo-primary);
      color: #fff;
      border-color: var(--ewo-primary);
      cursor: default;
    }
    .ewo-addon-card.selected {
      border: 2px solid var(--ewo-primary);
      box-shadow: 0 0 0 3px var(--ewo-secondary);
    }
  `;
  document.head.appendChild(style);

  // --- Lógica de selección de addons y animación ---
  let selectedAddons = JSON.parse(localStorage.getItem('ewo-selected-addons') || '[]');
  addonsSlider.querySelectorAll('.ewo-addon-btn').forEach(btn => {
    const addonId = btn.getAttribute('data-addon-id');
    const card = btn.closest('.ewo-addon-card');
    const isSelected = selectedAddons.includes(addonId);
    if (isSelected) {
      btn.classList.add('added');
      btn.textContent = 'Remove';
      card.classList.add('selected');
    } else {
      btn.classList.remove('added');
      btn.textContent = 'ADD TO CART';
      card.classList.remove('selected');
    }
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const idx = selectedAddons.indexOf(addonId);
      if (idx === -1) {
        // Agregar
        selectedAddons.push(addonId);
        localStorage.setItem('ewo-selected-addons', JSON.stringify(selectedAddons));
        btn.classList.add('added');
        btn.textContent = 'Remove';
        card.classList.add('selected');
        card.animate([
          { boxShadow: '0 0 0 0px var(--ewo-secondary)' },
          { boxShadow: '0 0 0 8px var(--ewo-secondary)' },
          { boxShadow: '0 0 0 0px var(--ewo-secondary)' }
        ], { duration: 500 });
      } else {
        // Quitar
        selectedAddons.splice(idx, 1);
        localStorage.setItem('ewo-selected-addons', JSON.stringify(selectedAddons));
        btn.classList.remove('added');
        btn.textContent = 'ADD TO CART';
        card.classList.remove('selected');
        card.animate([
          { boxShadow: '0 0 0 8px var(--ewo-alert)' },
          { boxShadow: '0 0 0 0px var(--ewo-alert)' }
        ], { duration: 400 });
      }
    });
  });

  // --- Slider de addons: scroll con flechas ---
  leftArrow.onclick = function(e) {
    e.preventDefault();
    addonsSlider.scrollBy({ left: -320, behavior: 'smooth' });
  };
  rightArrow.onclick = function(e) {
    e.preventDefault();
    addonsSlider.scrollBy({ left: 320, behavior: 'smooth' });
  };

  // --- Cierre del modal ---
  function closeModal() {
    document.body.classList.remove('ewo-modal-open');
    overlay.remove();
    style.remove();
  }
  closeBtn.onclick = closeModal;
  overlay.onclick = function(e) { if (e.target === overlay) closeModal(); };
  document.addEventListener('keydown', function escHandler(e) {
    if (e.key === 'Escape') { closeModal(); document.removeEventListener('keydown', escHandler); }
  });

  // --- Validación y guardado de datos ---
  reviewBtn.onclick = function(e) {
    e.preventDefault();
    const data = {};
    let valid = true;
    form.querySelectorAll('input,select').forEach(input => {
      if (input.required && !input.value) {
        input.style.borderColor = 'var(--ewo-alert)';
        valid = false;
      } else {
        input.style.borderColor = '#b5d2f7';
      }
      data[input.name] = input.value;
    });
    if (!valid) return;
    // Guardar datos en localStorage
    localStorage.setItem('ewo-user-data', JSON.stringify(data));
    // Redirigir a la página de carrito configurada
    const cartPageUrl = window.ewoLocationConfig && window.ewoLocationConfig.cartPage;
    if (cartPageUrl) {
      window.location.href = cartPageUrl;
    } else {
      alert('Cart page is not configured.');
    }
  };
}

// NOTA: Copia aquí toda la función openUserAddonsModal y helpers de cierre, validación, etc. No incluyas lógica del slider principal. 