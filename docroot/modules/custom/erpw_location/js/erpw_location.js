(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.location = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $('.path-delete-location .ui-icon-closethick').on(
          'click',
          function (event) {
            event.preventDefault();
            window.location.href =
              drupalSettings.erpw_location.redirect_to_manage;
          }
        );
        $('.path-update-location .ui-icon-closethick, .path-add-location .ui-icon-closethick')
          .on('click', function (event) {
            event.preventDefault();
            window.location.href =
            drupalSettings.erpw_location.redirect_to_manage_location;
          });
          const select = document.getElementById('select');
        });

      var termsArray = [];
      // Add array to browser local storage.
      $(document).ready(function () {
        localStorage.setItem(
          'termArray',
          JSON.stringify(drupalSettings.erpw_location.locations_array)
          );
        termsArray = drupalSettings.erpw_location.locations_array;
      });

      window.addEventListener(
        'load',
        function (e) {
          if (navigator.onLine) {
            termsArray = drupalSettings.erpw_location.locations_array;
          } else {
            termsArray = JSON.parse(localStorage.getItem('termArray') || '[]');
          }
        },
        false
      );
      window.addEventListener(
        'online',
        function (e) {
          termsArray = drupalSettings.erpw_location.locations_array;
        },
        false
      );

      window.addEventListener(
        'offline',
        function (e) {
          termsArray = JSON.parse(localStorage.getItem('termArray') || '[]');
        },
        false
      );

      // Adding options to the select lists.
      $('select.location_options').change(function (event) {
        event.preventDefault();
        $('#location-level-1 .level_1').empty();
        $('#location-level-2 .level_2').empty();
        $('#location-level-3 .level_3').empty();
        $('#location-level-4 .level_4').empty();
        var zeroTid = event.target.value;
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            for (const oneKey in zeroValue) {
              const oneValue = zeroValue[oneKey];
              // Setting first level options.
              if (oneKey == 'children') {
                $('#location-level-1').css({
                  display: 'block',
                });
                var select = $('#location-level-1 .level_1')[0];
                var level_1 = Drupal.t('Select ' + zeroValue['level_label']);
                document.querySelector(
                  '#location-level-1 label'
                ).textContent = level_1;
                $('#location-level-1 .level_1').empty();
                select.add(new Option(level_1, 0));
                for (const valueKey in oneValue) {
                  for (const name in oneValue[valueKey]) {
                    if (name == 'name') {
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
      $('#location-level-1 .level_1').change(function (event) {
        event.preventDefault();
        var zeroTid = $('select.location_options').val();
        var oneTid = $('#location-level-1 .level_1').val();
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            const newoptions =
              termsArray[zeroKey]['children'][oneTid]['children'];
            $('#location-level-2').css({
              display: 'block',
            });
            var select = $('#location-level-2 .level_2')[0];
            $('#location-level-2 .level_2').empty();
            var level_2 = Drupal.t(
              'Select ' + termsArray[zeroKey]['children'][oneTid]['level_label']
            );
            document.querySelector(
              '#location-level-2 label'
            ).textContent = level_2;
            select.add(new Option(level_2, 0));
            for (const newKey in newoptions) {
              select.add(new Option(newoptions[newKey]['name'], newKey));
            }
          }
        }
      });
      $('#location-level-2 .level_2').change(function (event) {
        event.preventDefault();
        var zeroTid = $('select.location_options').val();
        var oneTid = $('#location-level-1 .level_1').val();
        var twoTid = $('#location-level-2 .level_2').val();
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            const newoptions =
              termsArray[zeroKey]['children'][oneTid]['children'][twoTid][
                'children'
              ];
            $('#location-level-3').css({
              display: 'block',
            });
            $('#location-level-4').css({
              display: 'none',
            });
            var select = $('#location-level-3 .level_3')[0];
            $('#location-level-3 .level_3').empty();
            var level_3 = Drupal.t(
              'Select ' +
                termsArray[zeroKey]['children'][oneTid]['children'][twoTid][
                  'level_label'
                ]
            );
            document.querySelector(
              '#location-level-3 label'
            ).textContent = level_3;
            select.add(new Option(level_3, 0));
            for (const newKey in newoptions) {
              select.add(new Option(newoptions[newKey]['name'], newKey));
            }
          }
        }
      });
      $('#location-level-3 .level_3').change(function (event) {
        event.preventDefault();
        var zeroTid = $('select.location_options').val();
        var oneTid = $('#location-level-1 .level_1').val();
        var twoTid = $('#location-level-2 .level_2').val();
        var threeTid = $('#location-level-3 .level_3').val();
        for (const zeroKey in termsArray) {
          const zeroValue = termsArray[zeroKey];
          // checks if the parent id is equal to level zero terms.
          if (zeroKey == zeroTid) {
            const newoptions =
              termsArray[zeroKey]['children'][oneTid]['children'][twoTid][
                'children'
              ][threeTid];
            for (const keys in newoptions) {
              if (keys == 'children') {
                $('#location-level-4').css({
                  display: 'block',
                });
                var select = $('#location-level-4 .level_4')[0];
                $('#location-level-4 .level_4').empty();
                var level_4 = Drupal.t(
                  'Select ' +
                    termsArray[zeroKey]['children'][oneTid]['children'][twoTid][
                      'children'
                    ][threeTid]['level_label']
                );
                document.querySelector(
                  '#location-level-4 label'
                ).textContent = level_4;
                select.add(new Option(level_4, 0));
                for (const newKey in newoptions[keys]) {
                  select.add(
                    new Option(newoptions[keys][newKey]['name'], newKey)
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
