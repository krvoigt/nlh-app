var Thumbnails = {
    init: function () {
        var $thumbnails = $('.thumbnails');

        if ($thumbnails.length === 0) {
            return;
        }

        this.bindEvents();
    },

    bindEvents: function () {
        $('.thumbnails_link:not(.-current)').click(function () {
            window.location = $(this).attr('href') + window.location.hash;
            return false;
        });


        $('*[data-target="thumbnails"]').click(function () {
            $('img').lazyload({
                container: $('.thumbnails'),
                effect: 'fadeIn'
            });

        })
    }
};

Thumbnails.init();
