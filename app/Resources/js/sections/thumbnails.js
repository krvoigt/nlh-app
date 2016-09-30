var Thumbnails = {
    firstLoad: true,

    init: function () {
        var $thumbnails = $('.thumbnails');

        if ($thumbnails.length === 0) {
            return;
        }

        $current = $thumbnails.find('.thumbnails_item.-current');
        $thumbnails.scrollTop($current.position().top - 99);

        this.bindEvents();
    },

    bindEvents: function () {
        $('.thumbnails_link:not(.-current)').click(function () {
            window.location = $(this).attr('href') + window.location.hash;
            return false;
        });

        $('.viewer_control.-toggle-panel[data-target="thumbnails"]').click(this.lazyLoad.bind(this));
    },

    lazyLoad: function () {
        if (! this.firstLoad) {
            return;
        }

        this.firstLoad = false;

        $('img').lazyload({
            container: $('.thumbnails'),
            effect: 'fadeIn'
        });
    },
};

Thumbnails.init();
