(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpw_status = {
    attach(context, settings) {
      // Update user status
      function updateConnectionStatus(msg, connected) {
        const currentStatus = document.getElementById("status");
        const currentDot = document.getElementById("dot");
        if (connected) {
          currentStatus.innerHTML = "Online";
          currentStatus.classList.add("online");
          currentStatus.classList.remove("offline");
          currentDot.classList.add("green-dot");
          currentDot.classList.remove("red-dot");
        } else {
          currentStatus.innerHTML = "Offline";
          currentStatus.classList.add("offline");
          currentStatus.classList.remove("online");
          currentDot.classList.add("red-dot");
          currentDot.classList.remove("green-dot");
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
        false,
      );
      window.addEventListener(
        "online",
        function (e) {
          updateConnectionStatus("Online", true);
        },
        false,
      );

      window.addEventListener(
        "offline",
        function (e) {
          updateConnectionStatus("Offline", false);
        },
        false,
      );
    },
  };
})(jQuery, Drupal, drupalSettings);
