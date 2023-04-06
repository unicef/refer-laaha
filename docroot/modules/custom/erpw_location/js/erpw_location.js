(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.location = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(".path-delete-location .ui-icon-closethick").on(
          "click",
          function (event) {
            event.preventDefault();
            window.location.href =
              drupalSettings.erpw_location.redirect_to_manage;
          }
        );
        $(
          ".path-update-location .ui-icon-closethick, .path-add-location .ui-icon-closethick"
        ).on("click", function (event) {
          event.preventDefault();
          window.location.href =
            drupalSettings.erpw_location.redirect_to_manage_location;
        });
        const select = document.getElementById("select");
      });
      const termsArray = drupalSettings.erpw_location.locations_array;
      $("#edit-location-options").change(function (event) {
        event.preventDefault();
        var zeroTid = event.target.value;
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            for (const oneKey in zeroValue) {
              const oneValue = zeroValue[oneKey];
              // Setting first level options.
              if (oneKey == "children") {
                $("#location-level-1").css({
                  display: "block",
                });
                var select = $("#location-level-1 #edit-level-1")[0];
                $("#location-level-1 #edit-level-1").empty();
                select.add(new Option("Select Option", 0));
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
      $("#location-level-1 #edit-level-1").change(function (event) {
        event.preventDefault();
        var zeroTid = $("#edit-location-options").val();
        var oneTid = $("#location-level-1 #edit-level-1").val();
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            const newoptions =
              termsArray[zeroKey]["children"][oneTid]["children"];
            $("#location-level-2").css({
              display: "block",
            });
            var select = $("#location-level-2 #edit-level-2")[0];
            $("#location-level-2 #edit-level-2").empty();
            select.add(new Option("Select Option", 0));
            for (const newKey in newoptions) {
              select.add(new Option(newoptions[newKey]["name"], newKey));
            }
          }
        }
      });
      $("#location-level-2 #edit-level-2").change(function (event) {
        event.preventDefault();
        var zeroTid = $("#edit-location-options").val();
        var oneTid = $("#location-level-1 #edit-level-1").val();
        var twoTid = $("#location-level-2 #edit-level-2").val();
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            const newoptions =
              termsArray[zeroKey]["children"][oneTid]["children"][twoTid][
                "children"
              ];
            $("#location-level-3").css({
              display: "block",
            });
            $("#location-level-4").css({
              display: "none",
            });
            var select = $("#location-level-3 #edit-level-3")[0];
            $("#location-level-3 #edit-level-3").empty();
            select.add(new Option("Select Option", 0));
            for (const newKey in newoptions) {
              select.add(new Option(newoptions[newKey]["name"], newKey));
            }
          }
        }
      });
      $("#location-level-3 #edit-level-3").change(function (event) {
        event.preventDefault();
        var zeroTid = $("#edit-location-options").val();
        var oneTid = $("#location-level-1 #edit-level-1").val();
        var twoTid = $("#location-level-2 #edit-level-2").val();
        var threeTid = $("#location-level-3 #edit-level-3").val();
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            const newoptions =
              termsArray[zeroKey]["children"][oneTid]["children"][twoTid][
                "children"
              ][threeTid];
            for (const keys in newoptions) {
              if (keys == "children") {
                $("#location-level-4").css({
                  display: "block",
                });
                var select = $("#location-level-4 #edit-level-4")[0];
                $("#location-level-4 #edit-level-4").empty();
                select.add(new Option("Select Option", 0));
                for (const newKey in newoptions[keys]) {
                  select.add(
                    new Option(newoptions[keys][newKey]["name"], newKey)
                  );
                }
              }
            }
          }
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
