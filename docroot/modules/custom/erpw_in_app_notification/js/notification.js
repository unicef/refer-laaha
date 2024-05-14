(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.notificationHelper = {
    attach(context, settings) {
      // TODO
    },
  };
})(jQuery, Drupal, drupalSettings);

function openTab(evt, cityName) {
  let i;
  const tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  const tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}
