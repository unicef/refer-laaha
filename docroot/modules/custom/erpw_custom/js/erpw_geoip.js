/**
 * @file
 * JavaScript for geoip js code.
 */

 (function ($, Drupal, drupalSettings) {
  'use strict';
  $(document).ready(function () {

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition);
    }

    function showPosition(position) {
       var api_key = drupalSettings.api_key;
       var latitude = position.coords.latitude ;
       var longitude = position.coords.longitude;

       if (latitude != null || longitude != null){
       var url = "https://api.opencagedata.com/geocode/v1/json?key="+api_key+"&q="+latitude+"+"+longitude+"&pretty=1&no_annotations=1"
        $.ajax({url: url , success: function(result){
          var country_code = result['results'][0]['components']['country_code'];
          $('#country-dropdown option').each(function(){
              if(this.value != null){
                const domain_array = this.value.split("_");
                if (domain_array.indexOf(country_code) >= 0 || country_code == 'zw' && domain_array[0] == 'zm') {
                  $('#country-dropdown').val(this.value).trigger("change");
                }
              }
          });
        }});
      }
    }
  });

})(jQuery, Drupal, drupalSettings);

