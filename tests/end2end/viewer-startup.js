casper.test.begin('Viewer: Startup', function suite(test) {
    casper.start(config.base + '/id/' + config.docId, function () {
        var title = casper.fetchText('h1');
        test.assertEquals(title, config.docTitle, 'The correct document title is shown');
        var imageSrc = casper.getElementAttribute('.viewer_image > img', 'src');
        test.assertEquals(imageSrc, config.imageBase + '/' +  config.docId + '/500/0/00000001.jpg', 'The first page\'s scan is displayed');
    });

    casper.run(function () {
        test.done();
    });
});
