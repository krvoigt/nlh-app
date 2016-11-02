# NLH

## Requirements

You need PHP 7 with cURL, DOMDocument, gd and ImageMagick installed (`apt-get install php-curl php-xml php-gd php-imagick php-mbstring`).

Also see http://symfony.com/doc/current/reference/requirements.html.

## Installation

Install backend dependencies with `composer install`.

Install frontend dependencies with `npm install`, then run `gulp compile`.

## Running locally

Start the application with `php bin/console server:start`.

## Contribution

* Write [good commit messages](http://chris.beams.io/posts/git-commit/)!
* Include an issue id in your commit message.
* Run ```php-cs-fixer fix``` prior to committing.
* Run ```phpunit``` to ensure all tests (including your new tests) are passing.
