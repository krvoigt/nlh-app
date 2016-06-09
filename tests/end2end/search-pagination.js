// TODO: Would be nice to actually check the label, but it's a pain in the ass
// because the text contains lots of whitespace and line breaks. Maybe extend
// casper with a decent assertSelectorHasText method?

casper.test.begin('Search: Pagination', function suite(test) {
    casper.start(config.base, function () {
        test.assertExists('.pagination_item.-current:nth-child(3)', 'Page 1 should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur nächsten Seite');
        this.waitForSelector('.pagination_item.-current:nth-child(4)');
    }).then(function () {
        test.assert(true, 'Page 2 should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur letzten Seite');
        this.waitForSelector('.pagination_item.-current:nth-last-child(3)');
    }).then(function () {
        test.assert(true, 'The last page should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur vorigen Seite');
        this.waitForSelector('.pagination_item.-current:nth-last-child(4)');
    }).then(function () {
        test.assert(true, 'The second-to-last page should be highlighted');
    });

    casper.then(function () {
        this.clickLabel('Zur ersten Seite');
        this.waitForSelector('.pagination_item.-current:nth-child(3)');
    }).then(function () {
        test.assert(true, 'Page 1 should be highlighted again');
    });

    casper.run(function () {
        test.done();
    });
});
