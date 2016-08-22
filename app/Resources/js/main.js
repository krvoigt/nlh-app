$.fx.speeds._default = 250;

$('.download-pdf').click(function (e) {
    var ppn = $(this).data('ppn');

    DCPDF.generatePDF(ppn + ":00000001");
});
