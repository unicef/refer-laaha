(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwDeleteUser = {
    attach: function (context, settings) {
      $(".delete-user-button", context)
        .once("erpw-delete-user")
        .on("click", function (event) {
          event.preventDefault();
          var userId = $(this).data("user-id");
          var dialogOptions = {
            title: Drupal.t("Are you sure you want to delete the user?"),
            dialogClass: "delete-user-popup",
            buttons: {
              Proceed: function () {
                $(this).dialog("close");
                deleteUser(userId);
              },
              Cancel: function () {
                $(this).dialog("close");
              },
            },
          };
          var dialogElement = $(
            "<div class='message-delete-white'>" +
              Drupal.t("Click on proceed to delete or cancel to go back.") +
              "</div>"
          ).dialog(dialogOptions);
        });

      function deleteUser(userId) {
        var deleteUserUrl = "/delete-user/" + userId;
        // Perform the Ajax request to delete the user.
        $.ajax({
          url: deleteUserUrl,
          type: "POST",
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              var successDialogOptions = {
                title: Drupal.t("Deleted successfully"),
                dialogClass: "delete-user-popup",
                buttons: {
                  "Back to users list": function () {
                    $(this).dialog("close");
                    backToDashboard();
                  },
                },
              };
              var successDialogElement = $(
                "<div class='message-delete-white'>" +
                  response.message +
                  "</div>"
              ).dialog(successDialogOptions);
            } else {
               var successDialogOptions = {
                 title: Drupal.t("Access Denied!"),
                 dialogClass: "delete-user-popup",
                 buttons: {
                   "Back to users list": function () {
                     $(this).dialog("close");
                     backToDashboard();
                   },
                 },
               };
               var successDialogElement = $(
                 "<div class='message-delete-white'>" +
                   response.message +
                   "</div>"
               ).dialog(successDialogOptions);
            }
          },
        });
      }

      function backToDashboard() {
        // Redirect the user to the dashboard page.
        window.location.href = "/users-list";
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
