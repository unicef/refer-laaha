(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpw_webform_offline = {
    attach: function (context, settings) {
      var userStatus = "n/a";
      window.addEventListener(
        "load",
        function (e) {
          if (navigator.onLine) {
            userStatus = "online";
          } else {
            userStatus = "offline";
          }
        },
        false
      );
      window.addEventListener(
        "online",
        function (e) {
          userStatus = "online";
        },
        false
      );

      window.addEventListener(
        "offline",
        function (e) {
          userStatus = "offline";
        },
        false
      );
      var inputTexts = document.querySelectorAll("input[type=text]");
      inputTexts.forEach(function (inputText) {
        inputText.addEventListener("change", function (event) {
          localStorage.setItem(inputText.name, inputText.value);
        });
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
