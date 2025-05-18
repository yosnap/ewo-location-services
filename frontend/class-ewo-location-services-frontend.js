// --- AUTOCOMPLETADO DE DIRECCIONES ---
function initAlgoliaAutocomplete() {
  if (typeof window.places === 'function') {
    // ... inicialización Algolia ...
  } else {
    // Esperar a que cargue el script
    const interval = setInterval(function() {
      if (typeof window.places === 'function') {
        clearInterval(interval);
        // ... inicialización Algolia ...
      }
    }, 100);
  }
}
function initJQueryUIAutocomplete() {
  if ($.ui && $.ui.autocomplete) {
    // ... inicialización jQuery UI ...
  } else {
    // Esperar a que cargue jQuery UI
    const interval = setInterval(function() {
      if ($.ui && $.ui.autocomplete) {
        clearInterval(interval);
        // ... inicialización jQuery UI ...
      }
    }, 100);
  }
}
// Llama a la función adecuada según el proveedor
function initializeAutocompleteProvider() {
  const provider = window.ewoServiceListingOptions?.autocomplete_provider || 'nominatim';
  if (provider === 'algolia') {
    initAlgoliaAutocomplete();
  } else if (provider === 'nominatim') {
    initJQueryUIAutocomplete();
  }
  // ... lógica para Google y Mapbox ...
}
// Inicializar cuando se muestre el paso 1
window.showLocationSection = function() {
  // ... existente ...
  initializeAutocompleteProvider();
}; 