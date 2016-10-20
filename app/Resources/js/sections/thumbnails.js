var Thumbnails = {
    firstLoad: true,
    panel: $('.viewer_panel.-thumbnails'),

    init: function () {
        if (this.panel.length === 0) {
            return;
        }

        this.bindEvents();
    },

    bindEvents: function () {
        this.panel.find('.thumbnails_link[href]').click(function () {
            window.location = $(this).attr('href') + window.location.hash;
            return false;
        });

        $('.viewer_control.-toggle-panel.-thumbnails').click(this.lazyLoad.bind(this));
    },

    lazyLoad: function () {
        if (! this.firstLoad) {
            return;
        }

        this.firstLoad = false;

        // Scroll to current page
        var that = this;
        setTimeout(function () {
            var $current = that.panel.find('.thumbnails_item.-current');
            that.panel.scrollTop($current.position().top - 52);
        }, 0);

        $('img').lazyload({
            container: this.panel,
            effect: 'fadeIn'
        });
    },
};

Thumbnails.init();
