'use strict';

  const targetDiv = document.querySelector(".overlay-popup");
  const btn = document.querySelector(".pop-up");

  if (window.localStorage.getItem("key", "pop-up" )) {
    targetDiv.style.display = 'none';
  } else {
    targetDiv.style.display = 'block';
  }

  btn.addEventListener("click", function () {
    if (!window.localStorage.getItem("key", "pop-up" )) {
      window.localStorage.setItem("key", "pop-up" );
      targetDiv.style.display = 'none';
      window.speechSynthesis.cancel();
    }
  });

  // grab the UI elements to work with

  function getCookieValue(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
const play = document.querySelector('.play');
const pause = document.querySelector('.pause');
const resume = document.querySelector('.resume');
resume.hidden = true;
pause.hidden = true;
// add UI event handlers
play.addEventListener("click", () => {
let player_cookies = getCookieValue("player");
let country_cookies = getCookieValue("country-selector");
let voiceId = getCookieValue("voice");
let langId = getCookieValue("langid");
  var utterance = new SpeechSynthesisUtterance(player_cookies, country_cookies);
  if (langId == 'en-US') {
    langId = 'en-US';
    voiceId = 41;
  }
  if (voiceId) {
    var voices = speechSynthesis.getVoices();
    utterance.voice = voices[voiceId];
  }
  else {
    utterance.lang = langId;
  }
  play.hidden = true;
  resume.hidden = true;
  pause.hidden = false;
  speechSynthesis.speak(utterance);
  utterance.addEventListener('end', function (event) {
    speechSynthesis.cancel();
    play.hidden = false;
    resume.hidden = true;
    pause.hidden = true;
  });
});

pause.addEventListener("click", () => {
  speechSynthesis.pause();
  play.hidden = true;
  pause.hidden = true;
  resume.hidden = false;
});

resume.addEventListener("click", () => {
  // data.innerText = "resume";
  speechSynthesis.resume();
  play.hidden = true;
  resume.hidden = true;
  pause.hidden = false;
});

 // location selector page redirection
  /**
   * Get cookie value.
   */
    function getCookie(name) {
    function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
    var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
    return match ? match[1] : null;
  }

  // Redirect user to Language selector screen.
  let countryLocationCookie = getCookie('country-location-selector');
  if (countryLocationCookie !== "TRUE" && window.location.pathname !== "/country-selector" && window.location.pathname !== "/user/login") {
    window.location.href = "/country-selector";
  }

  $('.region-header a').each(function(){ 
    var oldUrl = $(this).attr("href"); // Get current url
    var newUrl = oldUrl + "?amp"; // Create new url
    $(this).attr("href", newUrl); // Set herf value
  });

  $(document).ready(function() {
    jQuery('form').get(0).reset(); //clear form data on page load
  });