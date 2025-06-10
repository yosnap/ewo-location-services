// Mostrar respuesta de oportunidad en consola en la página de Gracias

document.addEventListener('DOMContentLoaded', function () {
  if (window.location.pathname.includes('/thank-you')) {
    try {
      const resp = localStorage.getItem('ewo-opportunity-response');
      if (resp) {
        console.log('Opportunity API response:', JSON.parse(resp));
        localStorage.removeItem('ewo-opportunity-response');
      }
    } catch (e) {}
  }
}); 