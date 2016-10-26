// Inited by Viewer
var ViewerThumbnails = {
    container: $('.thumbnails'),
    isInited: false,

    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        $('.thumbnails_link[href]', this.container).click(function () {
            window.location = $(this).attr('href') + window.location.hash;
            return false;
        });

        Viewer.controls.togglePanel.filter('.-thumbnails').click(this.lazyLoad.bind(this));
    },

    lazyLoad: function () {
        if (this.isInited) {
            return;
        }

        this.isInited = true;

        // Scroll to current page
        var that = this;
        setTimeout(function () {
            var $current = $('.thumbnails_item.-current', this.container);
            // Scroll the panel instead of the thumbnail section
            that.container.parent().scrollTop($current.position().top - 52);
        }, 0);

        $('img', this.container).lazyload({
            container: this.container,
            effect: 'fadeIn'
        });
    },
};
