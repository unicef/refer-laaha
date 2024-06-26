(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.menu_list = {
    attach(context, settings) {
      $(".bottom-menu-icon").click(function () {
        $('[id^="block-erpw-main-menu"]').append(
          "<span class='close-popup'></span>",
        );
        $('[id^="block-erpw-main-menu"]').show();
        $(".overlay").show();

        $(".close-popup").click(function (e) {
          e.preventDefault();
          $('.overlay, [id^="block-erpw-main-menu"]').hide();
          $(".close-popup").remove();
        });
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
