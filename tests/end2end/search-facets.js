casper.test.begin('Search: Facets', function suite(test) {
    casper.start(config.base + '/suche', function () {
        test.assertExists('.facets', 'Facets exist');
    });

    casper.then(function () {
        this.clickLabel('Filter anzeigen');
        this.waitUntilVisible('.facets_body');
    }).then(function () {
        test.assert(true, 'Facets became visible');
    });

    casper.run(function () {
        test.done();
    });
});
