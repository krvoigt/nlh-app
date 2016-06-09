casper.test.begin('Viewer: Double page', function suite(test) {
    casper.start(config.base + '/id/' + config.docId, function () {
        this.fillSelectors('.viewer', {'.js-page-view': 'double'});
    });

    casper.waitFor(function check() {
        return (this.getCurrentUrl() === config.base + '/id/' + config.docId + '?showDoublePage=true');
    }).then( function () {
        test.assertEval(function() {
            return __utils__.findAll('.viewer_image > img').length === 1;
        }, 'Double page display is activated, but only one page is shown since we are on the first page');
    });

    casper.then(function () {
        casper.click('.coverflow_link:nth-child(2)');
    }).waitFor(function check() {
        return (this.getCurrentUrl() === config.base + '/id/' + config.docId + '?page=2&showDoublePage=true');
    }).then( function () {
        var pageNumber = casper.evaluate(function () {
            return $('.js-select-page option[selected]').text();
        });
        test.assertEquals(pageNumber, '2 | 3', 'Pages 2 and 3 should be loaded');
        var actualImageSrcs = [
            casper.getElementAttribute('.viewer_image > img:nth-child(1)', 'src'),
            casper.getElementAttribute('.viewer_image > img:nth-child(2)', 'src'),
        ];
        var expectedImageSrcs = [
            config.imageBase + '/' + config.docId + '/500/0/00000002.jpg',
            config.imageBase + '/' + config.docId + '/500/0/00000003.jpg',
        ];
        test.assertEquals(actualImageSrcs, expectedImageSrcs, 'The scans of pages 2 and 3 are being displayed');
    });

    casper.run(function () {
        test.done();
    });
});
