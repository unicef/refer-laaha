(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwDeleteUser = {
    attach: function (context, settings) {
      $(".delete-user-button", context)
        .once("erpw-delete-user")
        .on("click", function (event) {
          event.preventDefault();
          var userId = $(this).data("user-id");
          var dialogOptions = {
            title: "Are you sure you want to delete the user?",
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
              "Click on proceed to delete or cancel to go back." +
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
                title: "Deleted successfully",
                dialogClass: "delete-user-popup",
                buttons: {
                  "Back to dashboard": function () {
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
                 title: "Access Denied!",
                 dialogClass: "delete-user-popup",
                 buttons: {
                   "Back to dashboard": function () {
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
        window.location.href = "/dashboard";
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
