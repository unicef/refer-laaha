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
       if(latitude != null || longitude != null){
       var url = "https://api.opencagedata.com/geocode/v1/json?key="+api_key+"&q="+latitude+"+"+longitude+"&pretty=1&no_annotations=1"
        jQuery.ajax({url: url , success: function(result){
          var country_code = result['results'][0]['components']['country_code'];
          jQuery('#country-dropdown option').each(function(){
              if(this.value != null){
                const myArray = this.value.split("_");
                if(myArray[0] == country_code || myArray[1] == country_code || myArray[2] == country_code ){
                  jQuery('#country-dropdown').val(this.value).trigger("change");
                }
              }
          });
          setTimeout(
            function() {
              jQuery('#language-dropdown option').each(function(){
                  if(this.value != null && this.value == 'en'){
                    jQuery('#language-dropdown').val('en');
                  }
              });
            }, 5000);
          
        }});
      }
    }

  });

})(jQuery, Drupal, drupalSettings);

