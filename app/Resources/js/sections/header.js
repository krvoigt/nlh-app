var Header = {
    container: $('.header'),
    controls: {
        searchClose: $('.search_close', this.container),
        searchToggle: $('.header_search-toggle', this.container),
    },

    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        var that = this;

        this.controls.searchToggle.click(function () {
            $('.search', that.container).addClass('-show-popup');
            setTimeout(function () {
                $('.search_input:visible', that.container).focus();
            }, 100);
            return false;
        });

        this.controls.searchClose.click(function () {
            $('.search_input', that.container).blur();
            $('.search', that.container).removeClass('-show-popup');
            return false;
        });

        $('.search_input', this.container).focus(function () {
            if ($('.search.-show-popup').length > 0) {
                return;
            }
            $('.search').addClass('-show-popup');
        });

        $(window).click(function () {
            that.controls.searchClose.click();
        });

        $('.search', this.container).click(function (e) {
            e.stopPropagation();
        });

        // When Esc is pressed, clear search term if present, otherwise close search popup
        $(document).bind('keydown', function(e) {
            if (e.keyCode === 27) {
                if ($('.search_input:focus').length === 0 || $('.search_input').val() === '') {
                    $(window).click();
                } else {
                    $('.search_input').val('').change();
                }
                return false;
            }
        });
    },
}
