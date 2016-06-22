casper.test.begin('Viewer: Startup', function suite(test) {
    casper.start(config.base + '/id/' + config.docId, function () {
        var title = casper.fetchText('h1');
        test.assertEquals(title, config.docTitle, 'The correct document title is shown');
        var imageSrc = casper.getElementAttribute('.viewer_image > img', 'src');
        test.assertEquals(imageSrc, '/image/' + config.docId + ':00000001/full/1024,', 'The first page\'s scan is displayed');
    });

    casper.run(function () {
        test.done();
    });
});
