var Header = {
    container: $('.header'),
    controls: {
        searchClose: $('.search_close', '.root.-fixed'),
        searchToggle: $('.header_search-toggle', this.container),
    },

    init: function () {
        this.bindEvents();
    },

    bindEvents: function () {
        var that = this;

        this.controls.searchToggle.click(function () {
            $('.search', that.container).addClass('-show-popup').fadeIn();
            setTimeout(function () {
                $('.search_input:visible', that.container).focus();
            }, 10);
            return false;
        });

        this.controls.searchClose.click(function () {
            $('.search').fadeOut();
            return false;
        });

        $('.root.-fixed').click(function () {
            that.controls.searchClose.click();
        });

        $('.search', this.container).click(function (e) {
            e.stopPropagation();
        });
    },
}

Header.init();
