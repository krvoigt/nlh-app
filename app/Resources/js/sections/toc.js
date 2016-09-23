var TOC = {
    init: function () {
        var _this = this;
        $.get(window.location.origin + window.location.pathname + '/toc/', function (data) {
            $('.toc_content').html(data);
            _this.bindEvents();
        });
        this.isInited = true;
    },

    bindEvents: function () {
        $('.toc_link').click(function (e) {
            var $target = $(e.target);
            window.location = $target.attr('href') + window.location.hash;
            return false;
        });
    },

    isInited: false,
}

// TOC is initialized by viewer when TOC panel is first opened
