// TODO: Move this to another file.
// Remember that casper executes each test file in its own context.
casper.test.assertSelectorHasLabel = function(selector, label, message) {
    var actualCleanedLabel = casper.fetchText(selector).trim().replace(/(\s+)/gm, ' ');
    this.assertEquals(actualCleanedLabel, label, message);
};

casper.test.begin('Search', function suite(test) {
    var current = '.pagination:first-of-type .pagination_item.-current';

    casper.start(config.base + '/search', function () {
        test.assertExists('.facets', 'Facets exist');
        test.assertSelectorHasLabel(current, 'Aktuelle Seite: 1', 'Page 1 is highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur nächsten Seite');
        this.waitForSelector(current + ':nth-child(4)');
    }).then(function () {
        test.assertSelectorHasLabel(current, 'Aktuelle Seite: 2', 'Page 2 is highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur letzten Seite');
        this.waitForSelector(current + ':nth-last-child(3)');
    }).then(function () {
        test.assert(true, 'The last page is highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur vorigen Seite');
        this.waitForSelector(current + ':nth-last-child(4)');
    }).then(function () {
        test.assert(true, 'The second-to-last page is highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur ersten Seite');
        this.waitForSelector(current + ':nth-child(3)');
    }).then(function () {
        test.assertSelectorHasLabel(current, 'Aktuelle Seite: 1', 'Page 1 is highlighted again');
    });

    // Default resolution is 400x300 in which facets should be hidden
    casper.then(function () {
        var isHidden = ! this.visible('.facets_body')
        test.assert(isHidden, 'Facets are hidden');
    });

    casper.then(function () {
        this.clickLabel('Filter anzeigen');
        this.waitUntilVisible('.facets_body');
    }).then(function () {
        test.assert(true, 'Facets are visible');
    });

    casper.run(function () {
        test.done();
    });
});
