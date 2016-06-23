// TODO: Move this to another file.
// Not as easy as it sounds since casper executes each test file in its own context
casper.test.assertSelectorHasLabel = function(selector, label, message) {
    var actualCleanedLabel = casper.fetchText(selector).trim().replace(/(\s+)/gm, ' ');
    this.assertEquals(actualCleanedLabel, label, message);
};

casper.test.begin('Search: Pagination', function suite(test) {
    var current = '.pagination_item.-current';

    casper.start(config.base, function () {
        test.assertSelectorHasLabel(current, 'Aktuelle Seite: 1', 'Page 1 should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur nächsten Seite');
        this.waitForSelector(current + ':nth-child(4)');
    }).then(function () {
        test.assertSelectorHasLabel(current, 'Aktuelle Seite: 2', 'Page 2 should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur letzten Seite');
        this.waitForSelector(current + ':nth-last-child(3)');
    }).then(function () {
        test.assert(true, 'The last page should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur vorigen Seite');
        this.waitForSelector(current + ':nth-last-child(4)');
    }).then(function () {
        test.assert(true, 'The second-to-last page should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur ersten Seite');
        this.waitForSelector(current + ':nth-child(3)');
    }).then(function () {
        test.assertSelectorHasLabel(current, 'Aktuelle Seite: 1', 'Page 1 should be highlighted again');
    });

    casper.run(function () {
        test.done();
    });
});
