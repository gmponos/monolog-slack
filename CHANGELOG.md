# Changelog

All Notable changes to `monolog-slack` will be documented in this file see this [url](http://keepachangelog.com/)

## [NEXT]

### Changed
- Added version PHP 7.3 to travis.

## [v1.0.0] - 2018-11-12

This is the first stable release. No change has been made since 0.3.

## [v0.3.0] - 2018-06-25

### Added
- Added custom clients that implement the interface `Webthink\MonologSlack\Utility\ClientInterface` in order to communicate
with slack.
- Change the constructor of `SlackWebhooHandler` to accept a `Webthink\MonologSlack\Utility\ClientInterface` instead of
a guzzle client. If null is passed a `Webthink\MonologSlack\Utility\GuzzleClient` is initialized.

## [v0.2.0] - 2018-06-25

### Changed
- Changed the minimum required version of guzzle at composer.json in order to make the package compatible with any version 6 of guzzle.
- Changed the minimum required version of monolog at composer.json in order to make the package compatible with any version 1 of monolog.
- Added return types in many functions.
- Added types at arguments of `SlackWebhookHandler` constructor function.
- Interface `SlackFormatterInterface` now extends `FormatterInterface`
- [BC] Removed from constructor of `SlackWebhookHandler` the argument `$includeContextAndExtra`. Since this is package
is still at zero version we are allowed to do so.

## [v0.1.0] - 2018-03-24

### Added
- Created the initial functionality.
