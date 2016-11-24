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
            that.controls.tocPdf = $('.toc_pdf', this.container),
            that.bindEvents();
        });
        this.isInited = true;
    },

    bindEvents: function () {
        this.controls.tocLink.click(function (e) {
            setGetParameters({page: $(this).data('page')}, true);
            return false;
        });

        this.controls.tocPdf.click(function () {
            Viewer.controls.togglePanel.filter('[data-target=export]:not(.-active)').click();
            $('#physIDstart').val( $(this).data('start') );
            $('#physIDend').val( $(this).data('end') );
            return false;
        });
    },
}
