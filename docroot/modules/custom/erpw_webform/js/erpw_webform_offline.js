(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpw_webform_offline = {
    attach: function (context, settings) {
      var userStatus = "n/a";
      window.addEventListener(
        "load",
        function (e) {
          if (navigator.onLine) {
            userStatus = "online";
            submitDisable(userStatus);
          } else {
            userStatus = "offline";
            submitDisable(userStatus);
          }
        },
        false
      );
      window.addEventListener(
        "online",
        function (e) {
          userStatus = "online";
          submitDisable(userStatus);
        },
        false
      );

      window.addEventListener(
        "offline",
        function (e) {
          userStatus = "offline";
          submitDisable(userStatus);
        },
        false
      );
      // Define a function to populate the webform fields.
      function populateWebform() {
        // Get all the input fields of the webform.
        const inputFields = document.querySelectorAll(
          ".webform-submission-form .js-form-item input,.webform-submission-form .js-form-item select, .webform-submission-form .js-form-item textarea"
        );
        // Loop through each input field and set its value to the corresponding saved value, if available.
        inputFields.forEach((field) => {
          // Get the saved value of the field, if available.
          const savedValue = localStorage.getItem(field.name);
          // Check the type of the field and set its value accordingly.
          switch (field.nodeName) {
            case "SELECT":
              if (savedValue) {
                field.value = savedValue;
              }
              break;
            case "TEXTAREA":
              if (savedValue) {
                field.value = savedValue;
              }
              break;
            case "INPUT":
              switch (field.type) {
                case "text":
                case "email":
                case "tel":
                case "number":
                case "date":
                case "time":
                case "datetime-local":
                case "url":
                case "search":
                  if (savedValue) {
                    field.value = savedValue;
                  }
                  break;
                case "radio":
                case "checkbox":
                  if (field.value === savedValue) {
                    field.checked = true;
                  }
                  break;
              }
              break;
          }

          // Listen for changes in the field value and save the new value to localStorage.
          field.addEventListener("change", (event) => {
            // Check the type of the field and save its value accordingly.
            switch (event.target.nodeName) {
              case "SELECT":
                localStorage.setItem(event.target.name, event.target.value);
                break;
              case "TEXTAREA":
                localStorage.setItem(event.target.name, event.target.value);
                break;
              case "INPUT":
                switch (event.target.type) {
                  case "text":
                  case "email":
                  case "tel":
                  case "number":
                  case "date":
                  case "time":
                  case "datetime-local":
                  case "url":
                  case "search":
                    localStorage.setItem(event.target.name, event.target.value);
                    break;
                  case "radio":
                  case "checkbox":
                    if (event.target.checked) {
                      localStorage.setItem(
                        event.target.name,
                        event.target.value
                      );
                    }
                    break;
                }
                break;
            }
          });
        });

        const submitButton = document.querySelector(".webform-button--submit");

        // Listen for the submit button to be clicked and remove saved values from localStorage.
        submitButton.addEventListener("click", () => {
          // Loop through each input field and remove its saved value from localStorage.
          inputFields.forEach((field) => {
            localStorage.removeItem(field.name);
          });
        });
      }
      // Listen for the submit button to be clicked and remove saved values from localStorage.

      // Call the populateWebform function when the webform page is loaded.
      window.addEventListener("load", populateWebform);

      function submitDisable(userStatus) {
        const submitButton = document.querySelector(".webform-button--submit");
        if (userStatus == "online") {
          submitButton.disabled = false;
        } else {
          submitButton.disabled = "disabled";
        }
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
