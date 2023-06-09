(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.erpwWebformLocationList = {
    attach: function (context, settings) {
      $(document).ready(function () {
        var currentPath = drupalSettings.path.currentPath;
        if (drupalSettings.erpw_webform.defaultDomainRequired == 'Yes') {
          $(document).ready(function () {
            localStorage.setItem(
              'termArray',
              JSON.stringify(drupalSettings.erpw_webform.termsArray)
            );
            localStorage.setItem(
              'defaultDomain',
              drupalSettings.erpw_webform.defaultDomain
            );
            termsArray = drupalSettings.erpw_webform.termsArray;
            defaultDomain = drupalSettings.erpw_webform.defaultDomain;
          });

          window.addEventListener(
            'load',
            function (e) {
              if (navigator.onLine) {
                termsArray = drupalSettings.erpw_webform.termsArray;
                defaultDomain = drupalSettings.erpw_webform.defaultDomain;
              } else {
                termsArray = JSON.parse(
                  localStorage.getItem('termArray') || '[]'
                );
                defaultDomain = localStorage.getItem('defaultDomain');
              }
            },
            false
          );
          window.addEventListener(
            'online',
            function (e) {
              termsArray = drupalSettings.erpw_webform.termsArray;
              defaultDomain = drupalSettings.erpw_webform.defaultDomain;
            },
            false
          );

          window.addEventListener(
            'offline',
            function (e) {
              termsArray = JSON.parse(
                localStorage.getItem('termArray') || '[]'
              );
              defaultDomain = localStorage.getItem('defaultDomain');
            },
            false
          );
        } else {
          // Add array to browser local storage.
          $(document).ready(function () {
            localStorage.setItem(
              'termArray',
              JSON.stringify(drupalSettings.erpw_webform.termsArray)
            );
            termsArray = drupalSettings.erpw_webform.termsArray;
          });

          window.addEventListener(
            'load',
            function (e) {
              if (navigator.onLine) {
                termsArray = drupalSettings.erpw_webform.termsArray;
              } else {
                termsArray = JSON.parse(
                  localStorage.getItem('termArray') || '[]'
                );
              }
            },
            false
          );
          window.addEventListener(
            'online',
            function (e) {
              termsArray = drupalSettings.erpw_webform.termsArray;
            },
            false
          );

          window.addEventListener(
            'offline',
            function (e) {
              termsArray = JSON.parse(
                localStorage.getItem('termArray') || '[]'
              );
            },
            false
          );
        }
        if (currentPath.includes('admin/structure/webform/')) {
          defaultValues = drupalSettings.erpw_webform.default_location_values;
          termsArray = drupalSettings.erpw_webform.termsArray;
          countryID = defaultValues['location_options'];
          LevelOneID = defaultValues['level_1'];
          LevelTwoID = defaultValues['level_2'];
          LevelThreeID = defaultValues['level_3'];
          if (drupalSettings.erpw_webform.defaultDomainRequired == 'Yes') {
            $('select.location_options').parent().addClass(
              'disable-country-select-default'
            );
          } else {
            $('select.location_options').parent().removeClassClass(
              'disable-country-select-default'
            );
          }
          // Country
          if (defaultValues['location_options'] != '') {
            $('select.level_1').parent().css({
              display: 'block',
            });
            $('select.level_2').parent().css({
              display: 'none',
            });
            $('select.level_3').parent().css({
              display: 'none',
            });
            $('select.level_4').parent().css({
              display: 'none',
            });
            $('select.level_1').empty();
            level1_options = termsArray[countryID]['children'];
            var LevelOneselect = $('select.level_1')[0];
            // Set label for level.
            var level_1 = Drupal.t(
              'Select ' + termsArray[countryID]['level_label']
            );
            $('select.level_1').siblings('label').text(level_1);
            for (const newKey in level1_options) {
              LevelOneselect.add(
                new Option(level1_options[newKey]['name'], newKey)
              );
            }
          }
          // Level 1
          if (
            defaultValues['level_1'] != '' &&
            defaultValues['level_1'] != '0'
          ) {
            $('select.level_1').val(LevelOneID);
            $('select.level_2').parent().css({
              display: 'block',
            });
            $('select.level_2').empty();
            level2_options =
              termsArray[countryID]['children'][LevelOneID]['children'];
            var LevelTwoselect = $('select.level_2')[0];
            // Set label for level.
            var level_2 = Drupal.t(
              'Select ' +
                termsArray[countryID]['children'][LevelOneID]['level_label']
            );
            $('select.level_2').siblings('label').text(level_2);
            LevelTwoselect.add(new Option(level_2, 0));
            for (const newKey in level2_options) {
              LevelTwoselect.add(
                new Option(level2_options[newKey]['name'], newKey)
              );
            }
          }
          // Options for level2.
          if (
            defaultValues['level_2'] != '' &&
            defaultValues['level_2'] != '0'
          ) {
            $('select.level_2').val(LevelTwoID);
            $('select.level_3').parent().css({
              display: 'block',
            });
            $('select.level_3').empty();
            var select = $('select.level_3')[0];
            var level_3 = Drupal.t(
              'Select ' +
                termsArray[countryID]['children'][LevelOneID]['children'][
                  LevelTwoID
                ]['level_label']
            );
            $('select.level_3').siblings('label').text(level_3);
            select.add(new Option(level_3, 0));
            level3_options =
              termsArray[countryID]['children'][LevelOneID]['children'][
                LevelTwoID
              ]['children'];
            var LevelThreeselect = $('select.level_3')[0];
            // Set label for level.

            for (const newKey in level3_options) {
              LevelThreeselect.add(
                new Option(level3_options[newKey]['name'], newKey)
              );
            }
          }

          // Options for level3.
          if (
            defaultValues['level_3'] != '' &&
            defaultValues['level_3'] != '0'
          ) {
            $('select.level_4').parent().css({
              display: 'block',
            });
            $('select.level_3').val(LevelThreeID);
            $('select.level_4').parent().css({
              display: 'block',
            });
            $('select.level_4').empty();
            LevelFourID = defaultValues['level_4'];
            level4_options =
              termsArray[countryID]['children'][LevelOneID]['children'][
                LevelTwoID
              ]['children'][LevelThreeID]['children'];
            var LevelFourselect = $('select.level_4')[0];
            // Set label for level.
            var level_4 = Drupal.t(
              'Select ' +
                termsArray[countryID]['children'][LevelOneID]['children'][
                  LevelTwoID
                ]['children'][LevelThreeID]['level_label']
            );
            $('select.level_4').siblings('label').text(level_4);
            for (const newKey in level4_options) {
              LevelFourselect.add(
                new Option(level4_options[newKey]['name'], newKey)
              );
            }
          }

          // Options for level4.
          if (
            defaultValues['level_4'] != '' &&
            defaultValues['level_4'] != '0'
          ) {
            $('select.level_4').val(LevelFourID);
          }
        } else {
          if (drupalSettings.erpw_webform.defaultDomainRequired == 'Yes') {
            $('select.location_options').val(defaultDomain);
            $('select.location_options').parent().addClass(
              'disable-country-select-default'
            );
            $('select.level_1').empty();
            $('select.level_2').empty();
            $('select.level_3').empty();
            $('select.level_4').empty();
            $('select.level_1').parent().css({
              display: 'none',
            });
            $('select.level_2').parent().css({
              display: 'none',
            });
            $('select.level_3').parent().css({
              display: 'none',
            });
            $('select.level_4').parent().css({
              display: 'none',
            });
            var zeroTid = defaultDomain;
            console.log(zeroTid);
            console.log(termsArray);
            for (const zeroKey in termsArray) {
              const zeroValue = termsArray[zeroKey];
              console.log(zeroValue);
              // checks if the parent id is equal to level zero terms.
              if (zeroKey == zeroTid) {
                for (const oneKey in zeroValue) {
                  const oneValue = zeroValue[oneKey];
                  console.log(oneValue);
                  // Setting first level options.
                  if (oneKey == 'children') {
                    console.log('here');
                    $('select.level_1').parent().css({
                      display: 'block',
                    });
                    var select = $('select.level_1')[0];
                    var level_1 = Drupal.t(
                      'Select ' + zeroValue['level_label']
                    );
                    $('select.level_1').siblings('label').text(level_1);
                    $('select.level_1').empty();
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
          } else {
            $('select.location_options').parent().removeClass(
              'disable-country-select-default'
            );
            $('select.level_1').empty();
            $('select.level_2').empty();
            $('select.level_3').empty();
            $('select.level_4').empty();
            $('select.level_1').parent().css({
              display: 'none',
            });
            $('select.level_2').parent().css({
              display: 'none',
            });
            $('select.level_3').parent().css({
              display: 'none',
            });
            $('select.level_4').parent().css({
              display: 'none',
            });
          }
        }
        // Level zero country.
        // Adding options to the select lists.
        $('select.location_options').change(function (event) {
          event.preventDefault();
          $('select.level_1').empty();
          $('select.level_2').empty();
          $('select.level_3').empty();
          $('select.level_4').empty();
          $('select.level_1').parent().css({
            display: 'none',
          });
          $('select.level_2').parent().css({
            display: 'none',
          });
          $('select.level_3').parent().css({
            display: 'none',
          });
          $('select.level_4').parent().css({
            display: 'none',
          });
          var zeroTid = event.target.value;
          for (const zeroKey in termsArray) {
            const zeroValue = termsArray[zeroKey];
            // checks if the parent id is equal to level zero terms.
            if (zeroKey == zeroTid) {
              for (const oneKey in zeroValue) {
                const oneValue = zeroValue[oneKey];
                // Setting first level options.
                if (oneKey == 'children') {
                  $('select.level_1').parent().css({
                    display: 'block',
                  });
                  var select = $('select.level_1')[0];
                  var level_1 = Drupal.t('Select ' + zeroValue['level_label']);
                  $('select.level_1').siblings('label').text(level_1);
                  $('select.level_1').empty();
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
        // Level one.
        $('select.level_1').change(function (event) {
          event.preventDefault();
          var zeroTid = $('select.location_options').val();
          var oneTid = $('select.level_1').val();
          for (const zeroKey in termsArray) {
            const zeroValue = termsArray[zeroKey];
            // checks if the parent id is equal to level zero terms.
            if (zeroKey == zeroTid) {
              const newoptions =
                termsArray[zeroKey]['children'][oneTid]['children'];
              $('select.level_2').empty();
              $('select.level_2').parent().css({
                display: 'block',
              });
              $('select.level_3').parent().css({
                display: 'none',
              });
              $('select.level_4').parent().css({
                display: 'none',
              });
              var select = $('select.level_2')[0];
              var level_2 = Drupal.t(
                'Select ' +
                  termsArray[zeroKey]['children'][oneTid]['level_label']
              );
              $('select.level_2').siblings('label').text(level_2);
              select.add(new Option(level_2, 0));
              for (const newKey in newoptions) {
                select.add(new Option(newoptions[newKey]['name'], newKey));
              }
            }
          }
        });
        $('select.level_2').change(function (event) {
          event.preventDefault();
          var zeroTid = $('select.location_options').val();
          var oneTid = $('select.level_1').val();
          var twoTid = $('select.level_2').val();
          for (const zeroKey in termsArray) {
            const zeroValue = termsArray[zeroKey];
            // checks if the parent id is equal to level zero terms.
            if (zeroKey == zeroTid) {
              const newoptions =
                termsArray[zeroKey]['children'][oneTid]['children'][twoTid][
                  'children'
                ];
              $('select.level_3').parent().css({
                display: 'block',
              });
              $('select.level_4').parent().css({
                display: 'none',
              });
              var select = $('select.level_3')[0];
              $('select.level_3').empty();
              var level_3 = Drupal.t(
                'Select ' +
                  termsArray[zeroKey]['children'][oneTid]['children'][twoTid][
                    'level_label'
                  ]
              );
              $('select.level_3').siblings('label').text(level_3);
              select.add(new Option(level_3, 0));
              for (const newKey in newoptions) {
                select.add(new Option(newoptions[newKey]['name'], newKey));
              }
            }
          }
        });
        $('select.level_3').change(function (event) {
          event.preventDefault();
          var zeroTid = $('select.location_options').val();
          var oneTid = $('select.level_1').val();
          var twoTid = $('select.level_2').val();
          var threeTid = $('select.level_3').val();
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
                  $('select.level_4').parent().css({
                    display: 'block',
                  });
                  var select = $('select.level_4')[0];
                  $('select.level_4').empty();
                  var level_4 = Drupal.t(
                    'Select ' +
                      termsArray[zeroKey]['children'][oneTid]['children'][
                        twoTid
                      ]['children'][threeTid]['level_label']
                  );
                  $('select.level_4').siblings('label').text(level_4);
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
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
