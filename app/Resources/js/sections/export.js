$(function () {
    $('.download-pdf').click(function (e) {
        var ppn = $(this).data('ppn');
        // TODO: Sanitize. Add option to select arbitrary pages ("1, 3-6, 15").
        var physIDstart = parseInt($('#physIDstart').val());
        var physIDend = parseInt($('#physIDend').val());

        DCPDF.generatePDF(ppn, physIDstart, physIDend);
    });
});
