(function ($) {
    $('html').font_resize(
        {
            btnMinusId: '#font_resize-minus',
            btnDefaultId: '#font_resize-default',
            btnPlusId: '#font_resize-plus',
            btnMinusMaxHits: 3,
            btnPlusMaxHits: 1,
            sizeChange: 1
        }
    );

})(jQuery);