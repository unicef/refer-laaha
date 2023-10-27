(function ($, Drupal, drupalSettings, localforage) {
  var currentUserId = drupalSettings.user.uid;
  var appendOnce = true;
  localforage.config({
    driver: localforage.INDEXEDDB,
    name: "serviceFormsData",
    version: 1.0,
    storeName: "serviceFormsData",
  });
  localforageUserServiceCreated = localforage.createInstance({
    driver: localforage.INDEXEDDB,
    name: "userServiceCreated".concat(currentUserId),
    version: 1.0,
    storeName: "userServiceCreated".concat(currentUserId),
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
    var div = document.createElement("div");
    div.className = "offline-form-container";
    var form = document.createElement("form");
    form.classList.add("webform-submission-form");
    form.classList.add("webform-submission-add-form");
    form.classList.add("webform-submission-add-form-offline");
    var formID = event[0].dataset.key.replace(/_/g, "-");
    var formIDTitle = "";
    localforage.getItem(event[0].dataset.key).then(function (formData) {
      formIDTitle = formData["title"];
      for (let fieldKey in formData) {
        if (fieldKey == "elementsFlattened") {
          for (let elementKey in formData["elementsFlattened"]) {
            var invalidFields = [
              "service_type",
              "submission_domain",
              "erpw_workflow",
            ];
            if (!invalidFields.includes(elementKey)) {
              var element = formData["elementsFlattened"][elementKey];
              // Row element.
              var divElement = document.createElement("div");
              divElement.classList.add("js-form-item");
              divElement.classList.add("form-item");
              divElement.classList.add(
                "js-form-type-" +
                  elementKey.replace(/_/g, "-").replace(/-([^_]*)$/, "$1")
              );
              if (
                element.hasOwnProperty("#required") &&
                element["#required"] !== null
              ) {
                divElement.classList.add("form-item-required");
              }
              var label = document.createElement("label");
              label.classList.add("label");
              label.textContent = Drupal.t(element["#title"]);
              if (
                element.hasOwnProperty("#required") &&
                element["#required"] !== null
              ) {
                label.classList.add("js-form-required");
                label.classList.add("form-required");
              }

              divElement.appendChild(label);
              if (
                element["#type"] == "tel" ||
                element["#type"] == "textfield" ||
                element["#type"] == "email"
              ) {
                var label = document.createElement("label");
                label.classList.add("label");
                label.textContent = Drupal.t(element["#title"]);
                if (
                  element.hasOwnProperty("#required") &&
                  element["#required"] !== null
                ) {
                  label.classList.add("js-form-required");
                  label.classList.add("form-required");
                }

                var input = document.createElement("input");
                input.type =
                  element["#type"] == "textfield" ? "text" : element["#type"];
                input.className = "offline-input-field";
                input.name = elementKey;
                input.placeholder = Drupal.t("Enter ").concat(
                  Drupal.t(element["#title"])
                );

                divElement.appendChild(input);
                form.appendChild(divElement);
              } else if (element["#type"] == "select") {
                if (element["#webform_multiple"] == true) {
                  // Create a new div element for checkboxes
                  const checkboxDiv = document.createElement("div");
                  checkboxDiv.className = "offline-checkbox-list-wrapper";
                  // Define the options and their values
                  const options = element["#options"];

                  // Create and add checkboxes to the div element
                  for (const optionKey in options) {
                    const checkboxwrapperDiv = document.createElement("div");
                    checkboxwrapperDiv.className = "offline-checkboxes";
                    const checkbox = document.createElement("input");
                    checkbox.type = "checkbox";
                    checkbox.value = optionKey;
                    checkbox.id = `option-${optionKey}`;

                    const label = document.createElement("label");
                    label.htmlFor = `option-${optionKey}`;
                    label.textContent = options[optionKey];

                    checkboxwrapperDiv.appendChild(checkbox);
                    checkboxwrapperDiv.appendChild(label);
                    checkboxDiv.appendChild(checkboxwrapperDiv);
                  }

                  divElement.appendChild(checkboxDiv);
                  form.appendChild(divElement);
                } else {
                  // Create a new select element
                  const selectElement = document.createElement("select");
                  selectElement.className = "offline-select-list-wrapper";
                  // Define the options and their values
                  const options = element["#options"];

                  // Create and add options to the select element
                  for (const optionKey in options) {
                    const option = document.createElement("option");
                    option.value = optionKey;
                    option.text = options[optionKey];
                    selectElement.appendChild(option);
                  }

                  divElement.appendChild(selectElement);
                  form.appendChild(divElement);
                }
              } else if (element["#type"] == "radios") {
                // Create a new div element for radio buttons
                const radioDiv = document.createElement("div");
                radioDiv.className = "offline-radio-list-wrapper";
                // Define the options and their values
                const options = element["#options"];

                // Create and add radio buttons to the div element
                for (const optionKey in options) {
                  const radioWrapperDiv = document.createElement("div");
                  radioWrapperDiv.className = "offline-radios";
                  const radio = document.createElement("input");
                  radio.type = "radio";
                  radio.value = optionKey;
                  radio.id = `option-${optionKey}`;

                  const label = document.createElement("label");
                  label.htmlFor = `option-${optionKey}`;
                  label.textContent = options[optionKey];

                  radioWrapperDiv.appendChild(radio);
                  radioWrapperDiv.appendChild(label);
                  radioDiv.appendChild(radioWrapperDiv);
                }

                divElement.appendChild(radioDiv);
                form.appendChild(divElement);
              } else if (element["#type"] == "checkboxes") {
                // Create a new div element for checkboxes
                const checkboxDiv = document.createElement("div");
                checkboxDiv.className = "offline-checkbox-list-wrapper";
                // Define the options and their values
                const options = element["#options"];
                // Create and add checkboxes to the div element
                for (const optionKey in options) {
                  const checkboxwrapperDiv = document.createElement("div");
                  checkboxwrapperDiv.className = "offline-checkboxes";
                  const checkbox = document.createElement("input");
                  checkbox.type = "checkbox";
                  checkbox.value = optionKey;
                  checkbox.id = `option-${optionKey}`;

                  const label = document.createElement("label");
                  label.htmlFor = `option-${optionKey}`;
                  label.textContent = options[optionKey];
                  checkboxwrapperDiv.appendChild(checkbox);
                  checkboxwrapperDiv.appendChild(label);
                  checkboxDiv.appendChild(checkboxwrapperDiv);
                }

                divElement.appendChild(checkboxDiv);
                form.appendChild(divElement);
              } else if (
                element["#type"] == "webform_entity_select" &&
                element["#title"] == Drupal.t("Organisation")
              ) {
                // Create a new select element
                const selectElement = document.createElement("select");
                selectElement.className = "offline-select-list-wrapper";
                // Define the options and their values
                const options = drupalSettings.activeDomainOrganisation;
                // Create and add the initial "Select Organization" option
                const initialOption = document.createElement("option");
                initialOption.value = "";
                initialOption.text = Drupal.t("-- Select Organization --");
                selectElement.appendChild(initialOption);
                // Create and add options to the select element
                for (const optionKey in options) {
                  const option = document.createElement("option");
                  option.value = optionKey;
                  option.text = options[optionKey];
                  selectElement.appendChild(option);
                }

                divElement.appendChild(selectElement);
                form.appendChild(divElement);
              } else if (element["#type"] == "location_list_element") {
                termsArray = JSON.parse(
                  localStorage.getItem("termArray") || "[]"
                );
                var optionMarkup = ``;
                for (term in termsArray) {
                  optionMarkup += `<option value="${term}">${Drupal.t(
                    termsArray[term].name
                  )}</option>`;
                }
                // Create a temporary container element.
                const containerLocation = document.createElement("div");

                containerLocation.innerHTML = `<fieldset
                  data-drupal-selector="edit-location"
                  id="edit-location--wrapper"
                  class="location-list-element--wrapper fieldgroup form-composite webform-composite-hidden-title js-webform-type-location-list-element webform-type-location-list-element js-form-item form-item js-form-wrapper form-wrapper"
                >
                  <legend>
                    <span class="visually-hidden fieldset-legend"
                      >Location</span
                    >
                  </legend>
                  <div class="fieldset-wrapper">
                    <div
                      class="js-form-item form-item js-form-type-select form-type-select js-form-item-location-location-options form-item-location-location-options form-item-required"
                    >
                      <label
                        for="edit-location-location-options"
                        class="js-form-required form-required"
                        >Select Country</label
                      >

                      <select
                        class="location_options form-select"
                        data-drupal-selector="edit-location-location-options"
                        id="edit-location-location-options"
                        name="location[location_options]"
                        tabindex="-1"
                        aria-hidden="true"
                      >
                        <option value="" selected="selected">
                          Select Country
                        </option>
                        ${optionMarkup}
                      </select>
                    </div>

                    <div id="edit-location-details">
                      <div id="location-level-1">
                        <div
                          class="js-form-item form-item js-form-type-select form-type-select js-form-item-location-level-1 form-item-location-level-1"
                          style="display: none"
                        >
                          <label for="edit-location-level-1">Select province/district</label>

                          <select
                            class="level_1 form-select"
                            data-drupal-selector="edit-location-level-1"
                            id="edit-location-level-1"
                            name="location[level_1]"
                            tabindex="-1"
                            aria-hidden="true"
                          ></select>
                        </div>
                      </div>
                      <div id="location-level-2">
                        <div
                          class="js-form-item form-item js-form-type-select form-type-select js-form-item-location-level-2 form-item-location-level-2"
                          style="display: none"
                        >
                          <label for="edit-location-level-2">Select district/upazila</label>

                          <select
                            class="level_2 form-select"
                            data-drupal-selector="edit-location-level-2"
                            id="edit-location-level-2"
                            name="location[level_2]"
                            tabindex="-1"
                            aria-hidden="true"
                          ></select>
                        </div>
                      </div>
                      <div id="location-level-3">
                        <div
                          class="js-form-item form-item js-form-type-select form-type-select js-form-item-location-level-3 form-item-location-level-3"
                          style="display: none"
                        >
                          <label for="edit-location-level-3">Select Level 3 Label</label>

                          <select
                            class="level_3 form-select"
                            data-drupal-selector="edit-location-level-3"
                            id="edit-location-level-3"
                            name="location[level_3]"
                            tabindex="-1"
                            aria-hidden="true"
                          ></select>
                        </div>
                      </div>
                      <div id="location-level-4">
                        <div
                          class="js-form-item form-item js-form-type-select form-type-select js-form-item-location-level-4 form-item-location-level-4"
                          style="display: none"
                        >
                          <label for="edit-location-level-4">Select Level 4 Label</label>

                          <select
                            class="level_4 form-select"
                            data-drupal-selector="edit-location-level-4"
                            id="edit-location-level-4"
                            name="location[level_4]"
                            tabindex="-1"
                            aria-hidden="true"
                          ></select>
                        </div>
                      </div>
                    </div>
                  </div>
                </fieldset>
                `;

                // Append the container's child nodes to the form.
                for (let childNode of containerLocation.childNodes) {
                  form.appendChild(childNode);
                }
                // Adding options to the select lists.
                $("select.location_options").change(function (event) {
                  event.preventDefault();
                  $("select.level_1").empty();
                  $("select.level_2").empty();
                  $("select.level_3").empty();
                  $("select.level_4").empty();
                  $("select.level_1").parent().css({
                    display: "none",
                  });
                  $("select.level_2").parent().css({
                    display: "none",
                  });
                  $("select.level_3").parent().css({
                    display: "none",
                  });
                  $("select.level_4").parent().css({
                    display: "none",
                  });
                  var zeroTid = event.target.value;
                  for (const zeroKey in termsArray) {
                    const zeroValue = termsArray[zeroKey];
                    // checks if the parent id is equal to level zero terms.
                    if (zeroKey == zeroTid) {
                      for (const oneKey in zeroValue) {
                        const oneValue = zeroValue[oneKey];
                        // Setting first level options.
                        if (oneKey == "children") {
                          $("select.level_1").parent().css({
                            display: "block",
                          });
                          var select = $("select.level_1")[0];
                          var level_1 = Drupal.t(
                            "Select " + zeroValue["level_label"]
                          );
                          $("select.level_1").siblings("label").text(level_1);
                          $("select.level_1").empty();
                          select.add(new Option(level_1, 0));
                          for (const valueKey in oneValue) {
                            for (const name in oneValue[valueKey]) {
                              if (name == "name") {
                                select.add(
                                  new Option(oneValue[valueKey][name], valueKey)
                                );
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                });
                // Level one.
                $("select.level_1").change(function (event) {
                  event.preventDefault();
                  var zeroTid = $("select.location_options").val();
                  var oneTid = $("select.level_1").val();
                  for (const zeroKey in termsArray) {
                    const zeroValue = termsArray[zeroKey];
                    // checks if the parent id is equal to level zero terms.
                    if (zeroKey == zeroTid) {
                      const newoptions =
                        termsArray[zeroKey]["children"][oneTid]["children"];
                      $("select.level_2").empty();
                      $("select.level_2").parent().css({
                        display: "block",
                      });
                      $("select.level_3").parent().css({
                        display: "none",
                      });
                      $("select.level_4").parent().css({
                        display: "none",
                      });
                      var select = $("select.level_2")[0];
                      var level_2 = Drupal.t(
                        "Select " +
                          termsArray[zeroKey]["children"][oneTid]["level_label"]
                      );
                      $("select.level_2").siblings("label").text(level_2);
                      select.add(new Option(level_2, 0));
                      for (const newKey in newoptions) {
                        select.add(
                          new Option(newoptions[newKey]["name"], newKey)
                        );
                      }
                    }
                  }
                });
                $("select.level_2").change(function (event) {
                  event.preventDefault();
                  var zeroTid = $("select.location_options").val();
                  var oneTid = $("select.level_1").val();
                  var twoTid = $("select.level_2").val();
                  for (const zeroKey in termsArray) {
                    const zeroValue = termsArray[zeroKey];
                    // checks if the parent id is equal to level zero terms.
                    if (zeroKey == zeroTid) {
                      const newoptions =
                        termsArray[zeroKey]["children"][oneTid]["children"][
                          twoTid
                        ]["children"];
                      $("select.level_3").parent().css({
                        display: "block",
                      });
                      $("select.level_4").parent().css({
                        display: "none",
                      });
                      var select = $("select.level_3")[0];
                      $("select.level_3").empty();
                      var level_3 = Drupal.t(
                        "Select " +
                          termsArray[zeroKey]["children"][oneTid]["children"][
                            twoTid
                          ]["level_label"]
                      );
                      $("select.level_3").siblings("label").text(level_3);
                      select.add(new Option(level_3, 0));
                      for (const newKey in newoptions) {
                        select.add(
                          new Option(newoptions[newKey]["name"], newKey)
                        );
                      }
                    }
                  }
                });
                $("select.level_3").change(function (event) {
                  event.preventDefault();
                  var zeroTid = $("select.location_options").val();
                  var oneTid = $("select.level_1").val();
                  var twoTid = $("select.level_2").val();
                  var threeTid = $("select.level_3").val();
                  for (const zeroKey in termsArray) {
                    const zeroValue = termsArray[zeroKey];
                    // checks if the parent id is equal to level zero terms.
                    if (zeroKey == zeroTid) {
                      const newoptions =
                        termsArray[zeroKey]["children"][oneTid]["children"][
                          twoTid
                        ]["children"][threeTid];
                      for (const keys in newoptions) {
                        if (keys == "children") {
                          $("select.level_4").parent().css({
                            display: "block",
                          });
                          var select = $("select.level_4")[0];
                          $("select.level_4").empty();
                          var level_4 = Drupal.t(
                            "Select " +
                              termsArray[zeroKey]["children"][oneTid][
                                "children"
                              ][twoTid]["children"][threeTid]["level_label"]
                          );
                          $("select.level_4").siblings("label").text(level_4);
                          select.add(new Option(level_4, 0));
                          for (const newKey in newoptions[keys]) {
                            select.add(
                              new Option(
                                newoptions[keys][newKey]["name"],
                                newKey
                              )
                            );
                          }
                        }
                      }
                    }
                  }
                });
              }
            }
          }
        }
      }
    });
    div.appendChild(form);
    try {
      // Create a dialog box
      const dialog = $("<div>")
        .html(div)
        .dialog({
          title: Drupal.t(event[0].textContent).concat(
            Drupal.t(" Service Form - Offline.")
          ),
          modal: true,
          width: "auto !important",
          dialogClass: "offline-add-form-dialog-box",
          buttons: {
            Submit: {
              text: Drupal.t("Submit"),
              class: "offline-add-form-submit",
              click: function () {
                var form = document.querySelector(
                  ".webform-submission-add-form-offline"
                );
                const requiredFields = form.querySelectorAll(
                  ".form-item-required"
                );
                var globalRequired = true;
                requiredFields.forEach(function (requiredField) {
                  var inputElements = requiredField.querySelectorAll("input");
                  // Check if input elements are found
                  var elementChecked = false;
                  if (inputElements.length > 0) {
                    inputElements.forEach(function (inputElement) {
                      if (inputElement.getAttribute("type") == "checkbox") {
                        if (inputElement.checked) {
                          elementChecked = true;
                        }
                      } else if (inputElement.getAttribute("type") == "radio") {
                        if (inputElement.checked) {
                          elementChecked = true;
                        }
                      } else if (inputElement.value.trim()) {
                        elementChecked = true;
                      }
                    });
                  }
                  var selectElement = requiredField.querySelector("select");
                  if (selectElement) {
                    if (
                      selectElement.value !== "0" &&
                      selectElement.value !== ""
                    ) {
                      elementChecked = true;
                    }
                  }
                  if (!elementChecked) {
                    globalRequired = false;
                    var label = requiredField.querySelector("label");
                    var existingSpan = requiredField.querySelector(
                      ".offline-add-required-message"
                    );
                    if (!existingSpan) {
                      var span = document.createElement("span");
                      span.textContent = Drupal.t("This field is required");
                      span.className = "offline-add-required-message";

                      label.appendChild(span);
                    }
                  } else {
                    var existingSpan = requiredField.querySelector(
                      ".offline-add-required-message"
                    );
                    if (existingSpan) {
                      existingSpan.remove();
                    }
                  }
                  var elementToScrollTo = document.querySelector(
                    ".offline-add-required-message"
                  );
                  if (elementToScrollTo) {
                    elementToScrollTo.scrollIntoView({ behavior: "smooth" });
                  }
                });
                if (globalRequired) {
                  // Create an object to store the mapping
                  var contentEditableData = {};
                  // Find all elements of input within the form
                  const inputFields = form.querySelectorAll(
                    ".offline-input-field"
                  );
                  // Loop through the found input elements
                  inputFields.forEach(function (input) {
                    // Get the label text
                    var label = input.parentElement
                      .querySelector(".label")
                      .textContent.trim();
                    if (input.value.trim()) {
                      contentEditableData[label] = input.value.trim();
                    }
                  });

                  // Find all elements with checkboxes within the form
                  const checkboxFields = form.querySelectorAll(
                    ".offline-checkbox-list-wrapper"
                  );

                  checkboxFields.forEach(function (input) {
                    // Initialize an empty array to store checked values
                    var checkboxCheckedValues = [];
                    // Get the label text
                    var label = input.parentElement
                      .querySelector(".label")
                      .textContent.trim();
                    // Loop through the child nodes of the wrapper
                    input.childNodes.forEach(function (child) {
                      var checkbox = child.querySelector(
                        'input[type="checkbox"]'
                      );
                      if (checkbox.checked) {
                        // Get the value of the checked checkbox
                        var value = child.querySelector("label").textContent;
                        checkboxCheckedValues.push(value);
                      }
                    });
                    if (checkboxCheckedValues.length !== 0) {
                      // Add the label and checked values to the pairObject
                      contentEditableData[label] = checkboxCheckedValues;
                    }
                  });
                  // Find all elements of radio within the form
                  const radioFields = form.querySelectorAll(
                    ".offline-radio-list-wrapper"
                  );
                  radioFields.forEach(function (input) {
                    // Initialize an empty array to store checked values
                    var radiocheckedValues = "";
                    // Get the label text
                    var label = input.parentElement
                      .querySelector(".label")
                      .textContent.trim();
                    // Loop through the child nodes of the wrapper
                    input.childNodes.forEach(function (child) {
                      var radio = child.querySelector('input[type="radio"]');
                      if (radio) {
                        if (radio.checked) {
                          // Get the value of the checked checkbox
                          var value = child.querySelector("label").textContent;
                          radiocheckedValues = radiocheckedValues.concat(value);
                        }
                      }
                    });
                    if (radiocheckedValues !== "") {
                      // Add the label and checked values to the pairObject
                      contentEditableData[label] = radiocheckedValues;
                    }
                  });
                  const selectListFields = form.querySelectorAll(
                    ".offline-select-list-wrapper"
                  );

                  // Loop through the select list wrappers.
                  selectListFields.forEach(function (selectListWrapper) {
                    // Get the label text
                    var label = selectListWrapper.parentElement
                      .querySelector(".label")
                      .textContent.trim();

                    const selectedOption = selectListWrapper.value;

                    if (selectedOption !== "") {
                      if (label.toLowerCase() == "organisation") {
                        contentEditableData["orgID"] = selectedOption;
                        contentEditableData[label] = $(selectListWrapper)
                          .find("option:selected")
                          .text();
                      } else {
                        // Add the label and checked values to the pairObject
                        contentEditableData[label] = selectedOption;
                      }
                    }
                  });

                  // Loop through the location list select lists.
                  $(".location-list-element--wrapper").each(function () {
                    var location = [];
                    var locationText = "";
                    $(this)
                      .find("select")
                      .each(function () {
                        if (this.value !== "0" && this.value !== "") {
                          if (this.classList.contains("location_options")) {
                            location["location_options"] = this.value;
                            locationText = locationText.concat(
                              $(this).find("option:selected").text()
                            );
                          } else if (this.classList.contains("level_1")) {
                            location["level_1"] = this.value;
                            locationText = locationText
                              .concat(", ")
                              .concat($(this).find("option:selected").text());
                          } else if (this.classList.contains("level_2")) {
                            location["level_2"] = this.value;
                            locationText = locationText
                              .concat(", ")
                              .concat($(this).find("option:selected").text());
                          } else if (this.classList.contains("level_3")) {
                            location["level_3"] = this.value;
                            locationText = locationText
                              .concat(", ")
                              .concat($(this).find("option:selected").text());
                          } else if (this.classList.contains("level_4")) {
                            location["level_4"] = this.value;
                            locationText = locationText
                              .concat(", ")
                              .concat($(this).find("option:selected").text())
                              .concat(".");
                          }
                        }
                      });
                    // Add the label and checked values to the pairObject
                    contentEditableData["locationID"] = location;
                    contentEditableData["location"] = locationText;
                  });
                  contentEditableData["service_type"] = formID;
                  contentEditableData["service_type_title"] = formIDTitle;
                  localforageUserServiceCreated
                    .setItem(
                      event[0].textContent.concat(
                        Math.floor(10000 + Math.random() * 90000)
                      ),
                      contentEditableData
                    )
                    .then(() => {
                      console.log(`Data for new entry added.`);
                    })
                    .catch((error) =>
                      console.error(`Error updating data for new entry`, error)
                    );
                  contentEditableData = {};
                  // After submitting, close the dialog
                  // Clean up the dialog when it's closed
                  $(this).dialog("destroy").remove();
                }
              },
            },
            Close: {
              text: Drupal.t("Close"),
              click: function () {
                // Clean up the dialog when it's closed
                $(this).dialog("destroy").remove();
              },
            },
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
