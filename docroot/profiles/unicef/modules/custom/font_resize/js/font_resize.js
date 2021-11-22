(function ($) {
    $.fn.font_resize = function (options) {
        var $this = $(this);
        var defaults = {
            btnMinusId: '#font_resize-minus',
            btnDefaultId: '#font_resize-default',
            btnPlusId: '#font_resize-plus',
            btnMinusMaxHits: 10,
            btnPlusMaxHits: 10,
            sizeChange: 1
        };

        if (options) {
            options = $.extend(defaults, options)
        }
        ;

        var limite = new Array();
        var fontsize_pattern = new Array();

        $(this).each(function (i) {
            limite[i] = 0;
            fontsize_pattern[i];
        });

        $(options.btnMinusId + ', ' + options.btnDefaultId + ', ' + options.btnPlusId).removeAttr('href');
        $(options.btnMinusId + ', ' + options.btnDefaultId + ', ' + options.btnPlusId).css('cursor', 'pointer');

        /* A- */
        $(options.btnMinusId).click(function () {
            $(options.btnPlusId).removeClass('font_resize-disabled');
            $this.each(function (i) {
                if (limite[i] > (-(options.btnMinusMaxHits))) {
                    fontsize_pattern[i] = $(this).css('font-size');
                    fontsize_pattern[i] = fontsize_pattern[i].replace('px', '');
                    fontsize = $(this).css('font-size');
                    fontsize = parseInt(fontsize.replace('px', ''));
                    fontsize = fontsize - (options.sizeChange);
                    fontsize_pattern[i] = fontsize_pattern[i] - (limite[i] * options.sizeChange);
                    limite[i]--;
                    $(this).css('font-size', fontsize + 'px');
                    if (limite[i] == (-(options.btnMinusMaxHits))) {
                        $(options.btnMinusId).addClass('font_resize-disabled');
                    }
                }
            })
        });

        /* A */
        $(options.btnDefaultId).click(function () {
            $(options.btnMinusId).removeClass('font_resize-disabled');
            $(options.btnPlusId).removeClass('font_resize-disabled');
            $this.each(function (i) {
                limite[i] = 0;
                $(this).css('font-size', fontsize_pattern[i] + 'px');
            })
        });

        /* A+ */
        $(options.btnPlusId).click(function () {
            $(options.btnMinusId).removeClass('font_resize-disabled');
            $this.each(function (i) {
                if (limite[i] < options.btnPlusMaxHits) {
                    fontsize_pattern[i] = $(this).css('font-size');
                    fontsize_pattern[i] = fontsize_pattern[i].replace('px', '');
                    fontsize = $(this).css('font-size');
                    fontsize = parseInt(fontsize.replace('px', ''));
                    fontsize = fontsize + options.sizeChange;
                    fontsize_pattern[i] = fontsize_pattern[i] - (limite[i] * options.sizeChange);
                    limite[i]++;
                    $(this).css('font-size', fontsize + 'px');
                    if (limite[i] == options.btnPlusMaxHits) {
                        $(options.btnPlusId).addClass('font_resize-disabled');
                    }
                }
            })
        });
    };
})(jQuery);