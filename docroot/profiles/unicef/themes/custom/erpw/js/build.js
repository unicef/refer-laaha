!function(c,e){"use strict";e.behaviors.form_select={attach:function(e,o){0==c("select option:selected").val()&&c("select").css("color","#E0D7D7"),c("select").on("change",function(){return 0==c(this).children("option:selected").val()?c(this).css("color","#E0D7D7"):c(this).css("color","#444")}),c("form .form-submit").click(function(){c("form input").each(function(){""==c(this).val()?c(this).css("border","2px solid red"):c(this).css("border","1px solid #F3C1BF")}),c(".form-select").change(function(){0==c(this).children("option:selected").val()&&0==c(this).children("option").val()?c(this).css("border","2px solid red"):c(this).css("border","1px solid #F3C1BF")})})}}}(jQuery,Drupal,drupalSettings),function(c,e){"use strict";e.behaviors.icon_color_picker={attach:function(e,o){c(document).ready(function(){c(".field--name-field-service-type-icon .js-form-type-textfield").css("margin-top",120),c(".fip-box").click(function(){}),c(".color_field_widget_box__square").each(function(){var e=c(this).css("background-color");c(this).css("border-color",e)});var o=c("body").find(".icons-selector").children(".selector").find("span.selected-icon");c(".color_field_widget_box__square").click(function(){var e=c(this).css("background-color");c(o).css("background-color",e),console.log(o)});var e=c(".color_field_widget_box__square.active").css("background-color");c(o).css("background-color",e),c(".page-node-type-service-type .ui-dialog").find(".ui-dialog-title").html('<div class="review-msg">Deleted successfuly</div><div class="popup-content">The details has been successfully Deleted.</div>')})}}}(jQuery,Drupal,drupalSettings),function(c,e){"use strict";e.behaviors.menu_list={attach:function(e,o){c(".menu-icon").click(function(){c("#block-erpw-main-menu").append("<span class='close-popup'></span>"),c("#block-erpw-main-menu").show(),c(".overlay").show(),c(".close-popup").click(function(e){e.preventDefault(),c(".overlay, #block-erpw-main-menu").hide(),c(".close-popup").remove()})})}}}(jQuery,Drupal,drupalSettings),function(c,e){"use strict";e.behaviors.popup={attach:function(e,o){c(".sign-in-popup").length&&(c(".sign-in-popup").show(),c(".overlay").show(),c(".close-popup, .skip").click(function(e){e.preventDefault(),c(".sign-in-popup, .overlay").hide()})),c(".overlay").on("click",function(){c(".sign-in-popup").hide(),c("#block-erpw-main-menu").hide(),c(this).hide(),c(".close-popup").remove()}).appendTo(c(document.body))}}}(jQuery,Drupal,drupalSettings);