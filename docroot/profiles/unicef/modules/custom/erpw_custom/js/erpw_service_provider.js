(function ($, Drupal, window) {
    Drupal.behaviors.prev_next_button = {
      attach: function (context, settings) {
        $(".prev-btn").on('click', function(){
            $(".prev-btn").addClass('hidden');
            $(".next-btn").removeClass('hidden');
            $("#service-provider-step1").removeClass("hidden");
            $("#service-provider-step2").addClass("hidden");

        });
        $(".next-btn").on('click', function(){
            $(".next-btn").addClass('hidden');
            $(".prev-btn").removeClass('hidden');
            $("#service-provider-step2").removeClass("hidden");
            $("#service-provider-step1").addClass("hidden");
        });
      }
    };
  })(jQuery, Drupal, window);