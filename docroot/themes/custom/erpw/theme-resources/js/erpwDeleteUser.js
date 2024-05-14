(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.erpwDeleteUser = {
    attach(context, settings) {
      function backToDashboard() {
        // Redirect the user to the dashboard page.
        window.location.href = "/users-list";
      }

      function deleteUser(userId) {
        const deleteUserUrl = `/delete-user/${userId}`;
        // Perform the Ajax request to delete the user.
        $.ajax({
          url: deleteUserUrl,
          type: "POST",
          dataType: "json",
          success(response) {
            if (response.status === "success") {
              const successDialogOptions = {
                title: Drupal.t("Deleted successfully"),
                dialogClass: "delete-user-popup",
                buttons: {
                  "Back to users list": function () {
                    $(this).dialog("close");
                    backToDashboard();
                  },
                },
              };
              const successDialogElement = $(
                `<div class='message-delete-white'>${response.message}</div>`,
              ).dialog(successDialogOptions);
            } else {
              const successDialogOptions = {
                title: Drupal.t("Access Denied!"),
                dialogClass: "delete-user-popup",
                buttons: {
                  "Back to users list": function () {
                    $(this).dialog("close");
                    backToDashboard();
                  },
                },
              };
              const successDialogElement = $(
                `<div class='message-delete-white'>${response.message}</div>`,
              ).dialog(successDialogOptions);
            }
          },
        });
      }

      $(once("erpw-delete-user", ".delete-user-button", context)).on(
        "click",
        function (event) {
          event.preventDefault();
          const userId = $(this).data("user-id");
          const dialogOptions = {
            title: Drupal.t("Are you sure you want to delete the user?"),
            dialogClass: "delete-user-popup",
            buttons: {
              Proceed() {
                $(this).dialog("close");
                deleteUser(userId);
              },
              Cancel() {
                $(this).dialog("close");
              },
            },
          };
          const dialogElement = $(
            `<div class='message-delete-white'>${Drupal.t(
              "Click on proceed to delete or cancel to go back.",
            )}</div>`,
          ).dialog(dialogOptions);
        },
      );
    },
  };
})(jQuery, Drupal, drupalSettings, once);
