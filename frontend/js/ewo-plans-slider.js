import { openUserAddonsModal } from './ewo-user-addons-modal.js';

(function() {
  const root = document.getElementById('ewo-plans-slider-root');
  if (!root) return;

  // --- Utilidad: obtener coverage_code de localStorage/session ---
  function getCoverageCode() {
    let code = null;
    try {
      const last = localStorage.getItem('ewo-coverage-code');
      if (last) code = last;
    } catch (e) {}
    return code;
  }

  // --- Utilidad: obtener lat/lng desde ewo-location-coords ---
  function getLatLng() {
    let lat = null, lng = null;
    try {
      const coords = localStorage.getItem('ewo-location-coords');
      if (coords) {
        const obj = JSON.parse(coords);
        lat = obj.lat;
        lng = obj.lng;
      }
    } catch (e) {}
    return { lat, lng };
  }

  // --- Renderiza el slider con botón "CHOOSE PLAN" sobresaliendo ---
  function renderSlider(plans, allPackages) {
    root.innerHTML = '';
    const slider = document.createElement('div');
    slider.className = 'ewo-plans-slider';
    const track = document.createElement('div');
    track.className = 'ewo-plans-track';

    // Leer plan seleccionado de localStorage
    let selectedPlanId = localStorage.getItem('ewo-selected-plan-id');
    if (!selectedPlanId && plans[0]) selectedPlanId = plans[0].plan_id;

    plans.forEach((plan, idx) => {
      const card = document.createElement('div');
      card.className = 'ewo-plan-card';
      card.style.transition = 'background 0.2s, box-shadow 0.2s';

      // Banda superior: nombre del plan, sobresaliendo
      const planNameBand = `<div class=\"ewo-plan-name-band\">${plan.plan_name.toUpperCase()}</div>`;

      // Lista de features
      let features = '';
      if (Array.isArray(plan.features)) {
        features = plan.features.map(f => `<li>${f}</li>`).join('');
      } else if (plan.plan_description) {
        features = `<li>${plan.plan_description}</li>`;
      }

      // Botón "CHOOSE PLAN" sobresaliendo
      const isActive = String(plan.plan_id) === String(selectedPlanId);
      const chooseBtn = `<button class=\"ewo-plan-btn${isActive ? ' active' : ''}\" data-plan-id=\"${plan.plan_id}\">CHOOSE PLAN</button>`;

      card.innerHTML = `
        ${planNameBand}
        <div class=\"ewo-plan-speed\" style=\"color:var(--ewo-primary);font-size:1rem;font-weight:400;margin:1rem 0 0.5rem 0;text-align:center;\">UP TO<br><span style=\"color:var(--ewo-alert);font-weight:bold;font-size:2.5rem;\">${plan.speed_download_mbs || ''}Mbps</span></div>
        <div class=\"ewo-plan-price\" style=\"color:var(--ewo-primary);font-size:1.5rem;font-weight:300;margin-bottom:0.5rem;\">ONLY <span style=\"font-weight:bold;\">$${plan.price}</span>/MO</div>
        <div style=\"color:var(--ewo-primary);font-size:0.95rem;margin-bottom:0.5rem;font-weight:500;\">*Price includes $5 monthly auto-pay discount plus equipment fee (reflects autopay discount)</div>
        <ul class=\"ewo-plan-features\" style=\"color:var(--ewo-primary);font-size:1rem;text-align:left;margin:1rem 0 0 0;padding-left:1.2rem;\">${features}</ul>
        <div class=\"ewo-plan-btn-container\">${chooseBtn}</div>
      `;
      track.appendChild(card);
    });
    slider.appendChild(track);
    // Flechas fuera del track, centradas verticalmente
    const left = document.createElement('button');
    left.className = 'ewo-slider-arrow left';
    left.innerHTML = '&#8592;';
    left.onclick = () => { track.scrollBy({left: -340, behavior: 'smooth'}); };
    const right = document.createElement('button');
    right.className = 'ewo-slider-arrow right';
    right.innerHTML = '&#8594;';
    right.onclick = () => { track.scrollBy({left: 340, behavior: 'smooth'}); };
    slider.appendChild(left);
    slider.appendChild(right);
    root.appendChild(slider);

    // --- Estilos dinámicos ---
    const style = document.createElement('style');
    style.innerHTML = `
      .ewo-plans-slider {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: visible;
        min-height: 600px;
      }
      .ewo-plans-track {
        display: flex;
        gap: 2.5rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 2.5rem 0 2.5rem 0;
        position: relative;
        z-index: 1;
        overflow: visible;
      }
      .ewo-plan-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 16px rgba(32,63,154,0.08);
        border: 2px solid var(--ewo-primary);
        padding: 2rem 1.5rem 1.5rem 1.5rem;
        scroll-snap-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 520px;
        position: relative;
        margin: 0 0.5rem;
        overflow: visible;
      }
      .ewo-plan-card:hover {
        background: var(--ewo-secondary);
        box-shadow: 0 4px 24px rgba(32,63,154,0.13);
        border: 2px solid var(--ewo-primary);
      }
      .ewo-plan-name-band {
        position: absolute;
        top: -1rem;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.5em 1.7em;
        border-radius: 40px;
        font-size: .8rem;
        font-weight: 900;
        z-index: 3;
        box-shadow: 0 2px 8px rgba(32, 63, 154, 0.07);
        background: var(--ewo-secondary);
        color: var(--ewo-primary);
        border: 2px solid var(--ewo-secondary);
        min-width: 180px;
        text-align: center;
      }
      .ewo-plan-btn-container {
        position: absolute;
        left: 50%;
        bottom: -28px;
        transform: translateX(-50%);
        z-index: 4;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 0;
        height: 0;
      }
      .ewo-plan-btn {
        background: var(--ewo-secondary);
        color: var(--ewo-primary);
        border: 2px solid var(--ewo-primary);
        border-radius: 24px;
        padding: 0.7em 2em;
        font-size: 1.1rem;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(32,63,154,0.07);
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        min-width: 160px;
        min-height: 44px;
        outline: none;
      }
      .ewo-plan-btn.active {
        background: var(--ewo-primary);
        color: #fff;
        border: 2px solid var(--ewo-primary);
        box-shadow: 0 4px 16px rgba(32,63,154,0.18);
        z-index: 5;
      }
      .ewo-plan-btn:hover {
        background: var(--ewo-alert);
        color: #fff;
      }
      .ewo-plan-card ul {
        margin: 1rem 0 0 0;
        padding: 0 0 0 1.2rem;
        color: #444;
        font-size: 1rem;
        text-align: left;
      }
      .ewo-slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--ewo-secondary);
        color: #fff;
        border: 2px solid var(--ewo-primary);
        font-size: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(32,63,154,0.10);
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
      }
      .ewo-slider-arrow.left { left: -32px; }
      .ewo-slider-arrow.right { right: -32px; }
      .ewo-slider-arrow:hover { background: var(--ewo-primary); color: #fff; }
      @media (max-width: 900px) {
        .ewo-plans-slider { min-height: 480px; }
        .ewo-slider-arrow.left { left: 0; }
        .ewo-slider-arrow.right { right: 0; }
      }
      @media (max-width: 600px) {
        .ewo-plan-card { flex-basis: 90vw; min-width: 260px; }
        .ewo-plans-track { gap: 1rem; }
        .ewo-slider-arrow { width: 36px; height: 36px; font-size: 1.5rem; }
        .ewo-plan-btn, .ewo-plan-btn.active { min-width: 120px; min-height: 38px; font-size: 1rem; }
        .ewo-plan-btn-container { bottom: -18px; }
      }
    `;
    document.head.appendChild(style);

    // Hover dinámico para fondo secundario
    Array.from(track.children).forEach((card) => {
      card.addEventListener('mouseenter', () => card.classList.add('ewo-plan-card-featured'));
      card.addEventListener('mouseleave', () => card.classList.remove('ewo-plan-card-featured'));
    });

    // Evento de selección de plan (abre modal)
    root.querySelectorAll('.ewo-plan-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        const planId = this.getAttribute('data-plan-id');
        localStorage.setItem('ewo-selected-plan-id', planId);
        const planObj = plans.find(p => String(p.plan_id) === String(planId));
        openUserAddonsModal(planObj, allPackages);
      });
    });
  }

  // --- Cargar planes y addons vía AJAX ---
  async function loadPlans() {
    const code = getCoverageCode();
    const { lat, lng } = getLatLng();
    const nonce = window.ewoLocationServicesNonce || '';
    if (!code || !lat || !lng || !nonce) {
      root.innerHTML = '<div class="ewo-plans-slider-loading">Missing parameters.</div>';
      return;
    }
    root.innerHTML = '<div class="ewo-plans-slider-loading">Loading plans...</div>';
    try {
      const formData = new FormData();
      formData.append('action', 'ewo_get_packages');
      formData.append('coverage_code', code);
      formData.append('latitude', lat);
      formData.append('longitude', lng);
      formData.append('nonce', nonce);
      const res = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data && data.success && data.data && Array.isArray(data.data.packages)) {
        const allPackages = data.data.packages;
        const plans = allPackages.filter(p => !p.display_as_addon);
        // Guardar el array completo de paquetes en localStorage para el carrito
        localStorage.setItem('ewo-all-packages', JSON.stringify(allPackages));
        renderSlider(plans, allPackages);
      } else {
        root.innerHTML = '<div class="ewo-plans-slider-loading">No plans found for this location.</div>';
      }
    } catch (e) {
      root.innerHTML = '<div class="ewo-plans-slider-loading">Error loading plans.</div>';
    }
  }

  loadPlans();
})(); 