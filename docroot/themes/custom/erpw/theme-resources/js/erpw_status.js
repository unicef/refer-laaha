(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpw_status = {
    attach: function (context, settings) {
      //Update user status
      function updateConnectionStatus(msg, connected) {
        if (connected) {
          document.body.classList.add("online");
          document.body.classList.remove("offline");
        } else {
          document.body.classList.add("offline");
          document.body.classList.remove("online");
        }
      }
      window.addEventListener(
        "load",
        function (e) {
          if (navigator.onLine) {
            updateConnectionStatus("Online", true);
          } else {
            updateConnectionStatus("Offline", false);
          }
        },
        false
      );
      window.addEventListener(
        "online",
        function (e) {
          updateConnectionStatus("Online", true);
        },
        false
      );

      window.addEventListener(
        "offline",
        function (e) {
          updateConnectionStatus("Offline", false);
        },
        false
      );
    },
  };
})(jQuery, Drupal, drupalSettings);
