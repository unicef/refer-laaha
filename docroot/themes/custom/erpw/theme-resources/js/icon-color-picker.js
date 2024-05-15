(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.icon_color_picker = {
    attach(context, settings) {
      $(document).ready(function () {
        $(".field--name-field-service-type-icon .js-form-type-textfield").css(
          "margin-top",
          120,
        );
        $(".fip-box").click(function () {});

        $(".color_field_widget_box__square").each(function () {
          const bgcolor = $(this).css("background-color");
          $(this).css("border-color", bgcolor);
        });

        // add bg on selected icon
        const addBg = $("body")
          .find(".icons-selector")
          .children(".selector")
          .find("span.selected-icon");
        $(".color_field_widget_box__square").click(function () {
          const color = $(this).css("background-color");
          $(addBg).css("background-color", color);
          console.log(addBg);
        });
        const activeDiv = $(".color_field_widget_box__square.active").css(
          "background-color",
        );
        $(addBg).css("background-color", activeDiv);

        // Delete screen dialogtitle custom
        const popUpMsg = Drupal.t(
          '<div class="review-msg">Deleted successfully</div><div class="popup-content">The details has been successfully Deleted.</div>',
        );
        $(".page-node-type-service-type .delete-screen")
          .parents("body")
          .find(".ui-dialog .ui-dialog-title")
          .html(popUpMsg);
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
