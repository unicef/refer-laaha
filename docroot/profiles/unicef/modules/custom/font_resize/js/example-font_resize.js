(function ($) {
    $('html').font_resize(
        {
            btnMinusId: '#font_resize-minus',
            btnDefaultId: '#font_resize-default',
            btnPlusId: '#font_resize-plus',
            btnMinusMaxHits: 10,
            btnPlusMaxHits: 10,
            sizeChange: 1
        }
    );

})(jQuery);