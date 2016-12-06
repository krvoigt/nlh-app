var x = require('casper').selectXPath;

casper.test.begin('Viewer', function suite(test) {
    casper.start(config.base + '/id/' + config.docId, function () {
        var title = casper.fetchText('h1').trim();
        test.assertEquals(title, config.docTitle, 'The correct document title is shown');
    });

    casper.then(function () {
        this.waitForSelector('.leaflet-tile');
    }).then(function () {
        var imageSrc = casper.getElementAttribute('.scan_image img:first-child', 'src');
        this.capture('satan.png');
        test.assertEquals(imageSrc, '/image/eai1:1020A5B60209B2B0:101F4FC4B8B87E18/0,0,3994,4096/,512/0/default.jpg', 'The first page’s scan is displayed');
    });

    casper.then(function () {
        this.clickLabel('Nächste Seite');
        this.waitForSelector('.viewer_control.-pagination.-previous:not(.-disabled)');
    }).then(function () {
        test.assert(true, 'Page 2 is shown');
    });

    casper.then(function () {
        this.clickLabel('Letzte Seite');
        this.waitWhileSelector('.viewer_control.-pagination.-last');
    }).then(function () {
        test.assert(true, 'Last page is shown');
    });

    casper.then(function () {
        this.clickLabel('Erste Seite');
        this.waitWhileSelector('.viewer_control.-pagination.-first');
    }).then(function () {
        test.assert(true, 'First page is shown again');
    });

    casper.then(function() {
        this.click('.viewer_control.-change-view'); // "Ansicht"
        this.click('[data-target=toc]'); // "Inhalt"
        this.waitForSelector('.toc .toc_link');
    }).then(function () {
        test.assert(true, 'TOC is rendered')
    });

    casper.run(function () {
        test.done();
    });
});
