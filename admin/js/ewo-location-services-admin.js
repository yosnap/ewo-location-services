/**
 * Scripts para la administración del plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    // Hacer que las notificaciones sean descartables
    $(".notice-dismiss").on("click", function () {
      $(this).parent().fadeOut();
    });

    // Activar elementos UI si se necesitan
    if ($(".ewo-log-content").length) {
      // Scroll automático al final del log
      const logContent = $(".ewo-log-content");
      logContent.scrollTop(logContent.prop("scrollHeight"));
    }
  });
})(jQuery);
