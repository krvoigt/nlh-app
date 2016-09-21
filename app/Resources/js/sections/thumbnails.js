var Thumbnails = {
    init: function () {
        var $thumbnails = $('.thumbnails');

        if ($thumbnails.length === 0) {
            return;
        }

        $('img').lazyload({
            container: $thumbnails,
            effect: 'fadeIn',
        });

        this.bindEvents();
    },

    bindEvents: function () {
        $('.thumbnails_link:not(.-current)').click(function () {
            window.location = $(this).attr('href') + window.location.hash;
            return false;
        });
    },
};

Thumbnails.init();
