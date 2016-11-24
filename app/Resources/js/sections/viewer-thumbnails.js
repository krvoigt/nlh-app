// Inited by Viewer
var ViewerThumbnails = {
    container: $('.thumbnails'),
    isInited: false,

    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        $('.thumbnails_link[href]', this.container).click(function () {
            setGetParameters({page: $(this).data('page')}, true);
            return false;
        });

        Viewer.controls.togglePanel.filter('[data-target=thumbnails]').click(this.lazyLoad.bind(this));
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
            container: this.container.parent(),
            effect: 'fadeIn'
        });
    },
};
