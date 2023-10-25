!function(n,e){e.behaviors.location={attach:function(e,t){n(document).ready(function(){const e=document.getElementById("edit-proceed");e.addEventListener("click",()=>{const e=document.getElementById("download-message-box"),t=document.getElementById("download-message");t.innerHTML="File download successful, please wait for the download to finish.",e.style.display="flex"})})}}}(jQuery,Drupal,drupalSettings),function(o,s){s.behaviors.erpwDeleteUser={attach:function(e,t){function n(){window.location.href="/users-list"}o(".delete-user-button",e).once("erpw-delete-user").on("click",function(e){e.preventDefault();var t=o(this).data("user-id"),e={title:s.t("Are you sure you want to delete the user?"),dialogClass:"delete-user-popup",buttons:{Proceed:function(){o(this).dialog("close"),function(e){e="/delete-user/"+e;o.ajax({url:e,type:"POST",dataType:"json",success:function(e){var t;t="success"===e.status?{title:s.t("Deleted successfully"),dialogClass:"delete-user-popup",buttons:{"Back to users list":function(){o(this).dialog("close"),n()}}}:{title:s.t("Access Denied!"),dialogClass:"delete-user-popup",buttons:{"Back to users list":function(){o(this).dialog("close"),n()}}},o("<div class='message-delete-white'>"+e.message+"</div>").dialog(t)}})}(t)},Cancel:function(){o(this).dialog("close")}}};o("<div class='message-delete-white'>"+s.t("Click on proceed to delete or cancel to go back.")+"</div>").dialog(e)})}}}(jQuery,Drupal,drupalSettings),function(e){e.behaviors.erpw_status={attach:function(e,t){function n(e,t){var n=document.getElementById("status"),o=document.getElementById("dot");t?(n.innerHTML="Online",n.classList.add("online"),n.classList.remove("offline"),o.classList.add("green-dot"),o.classList.remove("red-dot")):(n.innerHTML="Offline",n.classList.add("offline"),n.classList.remove("online"),o.classList.add("red-dot"),o.classList.remove("green-dot"))}window.addEventListener("load",function(e){navigator.onLine?n(0,!0):n(0,!1)},!1),window.addEventListener("online",function(e){n(0,!0)},!1),window.addEventListener("offline",function(e){n(0,!1)},!1)}}}((jQuery,Drupal),drupalSettings),function(n,e){"use strict";e.behaviors.form_select={attach:function(e,t){0==n("select option:selected").val()&&n("select").css("color","#E0D7D7"),n("select").on("change",function(){return 0==n(this).children("option:selected").val()?n(this).css("color","#E0D7D7"):n(this).css("color","#444")}),n("form [id^='edit-submit'], form [id^='edit-next']").click(function(){n("form input[required='required']").each(function(){""==n(this).val()?n(this).css("border","2px solid red"):n(this).css("border","1px solid #F3C1BF")}),n(".form-select").change(function(){0==n(this).children("option:selected").val()&&0==n(this).children("option").val()?n(this).css("border","2px solid red"):n(this).css("border","1px solid #F3C1BF")}),n("label.error").parent(".select2-container .select2-selection").css("border","2px solid red")})}}}(jQuery,Drupal,drupalSettings),function(n,o){"use strict";o.behaviors.icon_color_picker={attach:function(e,t){n(document).ready(function(){n(".field--name-field-service-type-icon .js-form-type-textfield").css("margin-top",120),n(".fip-box").click(function(){}),n(".color_field_widget_box__square").each(function(){var e=n(this).css("background-color");n(this).css("border-color",e)});var t=n("body").find(".icons-selector").children(".selector").find("span.selected-icon");n(".color_field_widget_box__square").click(function(){var e=n(this).css("background-color");n(t).css("background-color",e),console.log(t)});var e=n(".color_field_widget_box__square.active").css("background-color");n(t).css("background-color",e);e=o.t('<div class="review-msg">Deleted successfully</div><div class="popup-content">The details has been successfully Deleted.</div>');n(".page-node-type-service-type .delete-screen").parents("body").find(".ui-dialog .ui-dialog-title").html(e)})}}}(jQuery,Drupal,drupalSettings),function(n,e){"use strict";e.behaviors.menu_list={attach:function(e,t){n(".bottom-menu-icon").click(function(){n('[id^="block-erpw-main-menu"]').append("<span class='close-popup'></span>"),n('[id^="block-erpw-main-menu"]').show(),n(".overlay").show(),n(".close-popup").click(function(e){e.preventDefault(),n('.overlay, [id^="block-erpw-main-menu"]').hide(),n(".close-popup").remove()})})}}}(jQuery,Drupal,drupalSettings),function(o,e){"use strict";e.behaviors.popup={attach:function(t,n){if(o(".sign-in-popup").length&&(o(".sign-in-popup").show(),o(".overlay").show(),o(".close-popup, .skip").click(function(e){e.preventDefault(),o(".sign-in-popup, .overlay").hide()})),o(".overlay").on("click",function(){o(".sign-in-popup").hide(),o('[id^="block-erpw-main-menu"]').hide(),o(this).hide(),o(".close-popup").remove()}).appendTo(o(document.body)),o("#block-pwaaddtohomescreen img").click(function(){document.cookie="pwacookie=true",localStorage.setItem("pwacookie","true"),o(".block-pwa-add-to-home-screen").addClass("hidden")}),"true"==localStorage.getItem("pwacookie")?o(".block-pwa-add-to-home-screen").addClass("hidden"):o(".block-pwa-add-to-home-screen").removeClass("hidden"),window.matchMedia("(display-mode: standalone)").matches&&o(".block-pwa-add-to-home-screen").addClass("hidden"),-1!=navigator.userAgent.indexOf("Firefox")){let e=document.createElement("button");n=n.pwaA2hs.pwaA2hsPrompt.button_text;e.innerHTML=n,o(".pwa-a2hs",t).once("showButton").each(function(){o(this).removeClass("pwa-a2hs hidden").addClass("pwa-a2hs-active show").append(e)})}}}}(jQuery,Drupal,drupalSettings),function(h,e){let g=!1;e.behaviors.erpw_service_rating={attach:function(e,c){const t=window.location.pathname;var n=t.split("/");const a=n[n.length-1];if(t.includes("admin/structure/webform/manage/webform_service_rating_")){const i=document.getElementById("block-erpw-help");null!=i&&(i.style.display="none");const d=document.getElementById("block-erpw-local-actions"),l=d.getElementsByClassName("action-links");if(null!=l&&(l[0].style.display="none"),null==document.getElementById("rating-action-block")){let e=document.createElement("div");e.classList.add("service-rating-action-links"),e.id="rating-action-block";let t=document.createElement("ul"),n="";null!=a&&(n="/"+a.split("_")[3]);var r=c.path.baseUrl+c.path.currentLanguage;let o=document.createElement("a");o.classList.add("add-new-question"),o.href=r+"/add-service-rating-question"+n,o.textContent="+ Add New Question";let s=document.createElement("li");s.appendChild(o);let i=document.createElement("button");i.id="service-rating-form-publish-btn",h.ajax({url:"/service-rating-form/"+a,method:"GET",success:function(e){console.log("DATA",e),document.getElementById("service-rating-form-publish-btn").textContent=e.form_status},error:function(e,t,n){console.error("Ajax request failed: "+n)}});let l=document.createElement("li");l.appendChild(i),t.appendChild(s),t.appendChild(l),e.appendChild(t),d.append(e)}}if(h("#service-rating-form-publish-btn").once("click").on("click",function(e){e.preventDefault(),h.ajax({url:"/service-rating-form/publish/"+a,method:"POST",success:function(e){document.getElementById("service-rating-form-publish-btn").textContent=e.form_button_text},error:function(e,t,n){console.error("Ajax request failed: "+n)}})}),console.log("HEY"),!g){if(t.includes("service-feedback-form")){const u=document.getElementsByClassName("fieldset-legend");if(null!=u&&0<u.length){let t=1;for(let e=0;e<u.length;e++)u[e].textContent=t.toString()+". "+u[e].textContent,t+=1}}g=!0}r=document.getElementsByClassName("average-service-ratings-box")[0];if(null!=r&&null!=r.children){var o=r.children[0]?.textContent;if(null!=o){const p=document.getElementById("overall-average-ratings").children;for(let e=o;e<5;e++)p[e].classList.add("star-empty")}}o=document.getElementsByClassName("service-ratings-services-list")[0];if(null!=o&&null!=o.children)for(rating of o.children){var s=rating.getElementsByClassName("service-average-rating")[0]?.textContent;if(null!=s){const m=document.getElementById("service-star-rating-"+s).children;for(let e=s;e<5;e++)m[e].classList.add("star-empty")}}o=document.getElementsByClassName("webform-forms");if(null!=o){o=o[0]?.children?.[1];if(null!=o)for(form of o?.children){form;const f=form?.children[0]?.children?.[0]?.attributes?.[0]?.value;f?.includes("webform-service-rating")&&(form.style.display="none")}}}}}(jQuery,Drupal,drupalSettings);