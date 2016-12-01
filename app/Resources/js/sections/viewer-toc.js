// Inited by viewer when TOC panel is first opened
var ViewerToc = {
    container: $('.toc'),
    controls: {},
    isInited: false,

    init: function () {
        var that = this;
        $.get(window.location.origin + window.location.pathname + '/toc/', function (data) {
            $('.toc_content', this.container).html(data);
            that.controls.tocLink = $('.toc_link, .toc_page-number', this.container),
            that.bindEvents();
        });
        this.isInited = true;
    },

    bindEvents: function () {
        this.controls.tocLink.click(function (e) {
            window.location = $(this).attr('href') + '&origin=' + encodeURIComponent($('#origin').attr('href')) + window.location.hash;
            return false;
        });
    },
}
