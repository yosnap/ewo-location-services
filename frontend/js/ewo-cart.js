// Carrito visual EWO
(function() {
  const root = document.getElementById('ewo-cart-root');
  if (!root) return;

  // Obtener datos del carrito
  const planId = localStorage.getItem('ewo-selected-plan-id');
  const addonsIds = JSON.parse(localStorage.getItem('ewo-selected-addons') || '[]');
  const userData = JSON.parse(localStorage.getItem('ewo-user-data') || '{}');
  // Los detalles de los planes y addons deben estar en localStorage (payload de paquetes)
  const allPackages = JSON.parse(localStorage.getItem('ewo-all-packages') || '[]');

  // Buscar plan principal
  const plan = allPackages.find(p => String(p.plan_id) === String(planId));
  // Buscar addons (comparación robusta de IDs)
  const addons = allPackages.filter(p => addonsIds.map(String).includes(String(p.plan_id)));

  // Logs de depuración
  console.log('planId:', planId);
  console.log('addonsIds:', addonsIds);
  console.log('allPackages:', allPackages);
  console.log('plan:', plan);
  console.log('addons:', addons);

  // Si el carrito está vacío
  if (!plan && addons.length === 0) {
    root.innerHTML = `<div class="ewo-cart-empty">
      <p>You have no items in your cart.</p>
      <a href="/" class="ewo-cart-home-link">Go to Home</a>
    </div>`;
    return;
  }

  // Renderizar tabla
  let html = '';
  html += `<table class="ewo-cart-table">
    <thead><tr><th>Products</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>`;
  if (plan) {
    html += `<tr><td>${plan.plan_name}</td><td>$${plan.price}</td><td>1</td><td>$${plan.price}</td></tr>`;
  }
  addons.forEach(addon => {
    html += `<tr><td>${addon.plan_name}</td><td>$${addon.price}</td><td>1</td><td>$${addon.price}</td></tr>`;
  });
  // Producto fijo Equipment Fee
  html += `<tr><td>Equipment Fee</td><td>$0.00</td><td>1</td><td>$0.00</td></tr>`;
  html += `</tbody></table>`;

  // Promo code row (fuera de la tabla, alineado)
  html += `<div class="ewo-cart-promo-row">
    <input type="text" class="ewo-cart-promo" placeholder="Promo Code">
    <button class="ewo-cart-promo-btn">APPLY PROMO CODE</button>
  </div>`;

  // Calcular totales
  let subtotal = 0;
  if (plan) subtotal += parseFloat(plan.price);
  addons.forEach(addon => { subtotal += parseFloat(addon.price); });
  let tax = subtotal * 0.00; // Solo visual
  let total = subtotal + tax;

  html += `<div class="ewo-cart-totals">
    <div class="ewo-cart-totals-title">Basket Total</div>
    <table class="ewo-cart-totals-table"><tbody>
      <tr><td>Subtotal</td><td class="ewo-cart-totals-value">$${subtotal.toFixed(2)}</td></tr>
      <tr><td>Tax</td><td class="ewo-cart-totals-value">$${tax.toFixed(2)}</td></tr>
      <tr><td>Total</td><td class="ewo-cart-totals-value">$${total.toFixed(2)}</td></tr>
    </tbody></table>
    <div class="ewo-cart-totals-btn-row">
      <button class="ewo-cart-proceed-btn">PROCEED TO INSTALLATION</button>
    </div>
  </div>`;

  root.innerHTML = html;

  // Botón de proceed
  const proceedBtn = root.querySelector('.ewo-cart-proceed-btn');
  if (proceedBtn) {
    proceedBtn.onclick = function() {
      const installationUrl = window.ewoLocationConfig && window.ewoLocationConfig.installationPage;
      if (installationUrl) {
        window.location.href = installationUrl;
      } else {
        alert('Installation page is not configured.');
      }
    };
  }
})(); 