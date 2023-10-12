(function ($, Drupal, drupalSettings, localforage) {
  var currentUserId = drupalSettings.user.uid;
  var appendOnce = true;
  localforage.config({
    driver: localforage.INDEXEDDB,
    name: "serviceFormsData",
    version: 1.0,
    storeName: "serviceFormsData",
  });
  $(".new-service-type a").on("click", function (event) {
    if (!navigator.onLine) {
      event.preventDefault();
      if (appendOnce) {
        $(this).parent().width(300);
        const div = document.createElement("div");
        div.classList.add("offline-service-forms-list");
        const heading = document.createElement("h4");
        heading.classList.add("details-heading");
        heading.textContent = Drupal.t("Select the service type.");
        div.appendChild(heading);
        localforage.iterate(function (valueData, key, iterationNumber) {
          var tpa = valueData.third_party_settings;

          if (tpa) {
            var mapping = tpa.erpw_webform.webform_service_type_map;
            if (mapping.hasOwnProperty(drupalSettings.activeDomain)) {
              // Create the div structure
              const divRow = document.createElement("div");
              divRow.classList.add("forms-row");
              divRow.dataset.key = key;
              divRow.innerHTML = `
              <div class="service-providers-submission-row select-service-type-webform">
                  <div class="row-header">
                    <div class="service-type-color-logo-container">
                      <div class="service-type-org" data-key=${key}>${valueData.title}</div>
                    </div>
                  </div>
              </div>`;
              div.appendChild(divRow);
            }
          }
        });
        $(this).parent().append(div);
        appendOnce = false;
      }
    }
  });

  $(document).on("click", ".service-type-org", function (event) {
    if (!navigator.onLine) {
      event.preventDefault();
      showOfflineAddForm($(this));
    }
  });

  function showOfflineAddForm(event) {
    // Implement logic to show the offline add form here.
    localforage.getItem(event[0].dataset.key).then(function (formData) {
      for (let fieldKey in formData) {
        if (fieldKey == "elementsFlattened") {
          console.log(formData["elementsFlattened"]);
        }
      }
    });
    try {
      // Create a dialog box
      const dialog = $("<div>")
        .html("<span>Here</span>")
        .dialog({
          title: "Offline Form",
          modal: true,
          width: 500,
          buttons: {
            Submit: function () {
              // Handle form submission here (you can send data to the server via Ajax)
              // After submitting, close the dialog
              $(this).dialog("close");
            },
            Close: function () {
              // Close the dialog without submitting
              $(this).dialog("close");
            },
          },
          close: function () {
            // Clean up the dialog when it's closed
            $(this).dialog("destroy").remove();
          },
        });
    } catch (error) {
      console.error("Invalid form key!", error);
    }
  }
  Drupal.behaviors.erpwOfflineServiceAdd = {
    attach: function (context, settings) {},
  };
})(jQuery, Drupal, drupalSettings, localforage);
