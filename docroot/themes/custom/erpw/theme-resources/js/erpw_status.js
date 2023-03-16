(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpw_status = {
    attach: function (context, settings) {
      //Update user status
      function updateConnectionStatus(msg, connected) {
        var current_status = document.getElementById("status");
        var current_dot = document.getElementById("dot");
        if (connected) {
          current_status.innerHTML = "Online";
          current_status.classList.add("online");
          current_status.classList.remove("offline");
          current_dot.classList.add("green-dot");
          current_dot.classList.remove("red-dot");
        } else {
          current_status.innerHTML = "Offline";
          current_status.classList.add("offline");
          current_status.classList.remove("online");
          current_dot.classList.add("red-dot");
          current_dot.classList.remove("green-dot");
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
