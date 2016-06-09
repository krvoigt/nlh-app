casper.test.begin('Search: Facets', function suite(test) {
    casper.start(config.base, function () {
        test.assertExists('.facetsBody', 'Facets exist');
    }).then(function () {
        this.clickLabel('Filter anzeigen');
        this.waitUntilVisible('.facetsBody');
    }).then(function () {
        test.assert(true, 'Facets are visible');
    });

    casper.run(function () {
        test.done();
    });
});
