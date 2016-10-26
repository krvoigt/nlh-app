casper.test.begin('Viewer: Startup', function suite(test) {
    casper.start(config.base + '/id/' + config.docId, function () {
        var title = casper.fetchText('h1').trim();
        test.assertEquals(title, config.docTitle, 'The correct document title is shown');
    });

    casper.then(function () {
        this.waitForSelector('.leaflet-tile');
    }).then(function () {
        var imageSrc = casper.getElementAttribute('.scan_image img:first-child', 'src');
        test.assertEquals(imageSrc, '/image/' + config.docId + ':00000001/0,0,1480,2048/,512/0/default.jpg', 'The first page\'s scan is displayed');
    });

    casper.run(function () {
        test.done();
    });
});
