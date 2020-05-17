# Changelog

All Notable changes to `monolog-slack` will be documented in this file see this [url](http://keepachangelog.com/)

## [v2.1.0] - 2020-05-16

### Added
- Ability to provide channel to formatters. [#16](https://github.com/gmponos/monolog-slack/pull/16)

### Fix
- Make the AbstractSlackAttachmentFormatter compatible with version 1 [#19](https://github.com/gmponos/monolog-slack/pull/19)
- Trim colon from emoji string at SlackLineFormatter. [#15](https://github.com/gmponos/monolog-slack/pull/15)

## [v2.0.0] - 2020-01-03

First stable release of v2.0. No changes from the previous release

## [v2.0.0-RC1] - 2019-12-25

### Changes
- [BC] Minimum required version is PHP 7.2
- [BC] Minimum required version is monolog 2.0 
- [BC] Remove custom Client implementation. Since PSR-18 exists I decided to use a standard client instead of a custom implementation.
    - Everything under `Webthink\MonologSlack\Utility` has been removedWhatFailureGroupHandler
    - SlackWebhookHandler only accepts a `Psr\Http\Client\ClientInterface`
- [BC] The `SlackWebhookHandler` previously was silently failing in case the client was throwing an exception. This behaviour has changed.
if you still want the handler to fail silently you will have to wrap it with the `WhatFailureGroupHandler` of monolog
- [BC] All classes are made final.

## [v1.3.0] - 2019-12-13

### Changes
- Added support for Monolog 2
- Test against PHP 7.4

## [v1.2.0] - 2019-12-04

### Deprecated
- Deprecated the custom clients that implement `Webthink\MonologSlack\Utility\ClientInterface`. Use a PSR-18 instead.
- Passing `$username` as argument to `SlackWebhookHandler` is deprecated and it will be removed on 2.x. Instead initialize 
your own own formatter and set it to the handler.  
- Passing `$useCustomEmoji` as argument to `SlackWebhookHandler` is deprecated and it will be removed on 2.x. Instead initialize 
your own own formatter and set it to the handler.

### Changes
- Allow the constructor of `SlackWebhookHandler` to pass as level `string|int`. Before it was only `int`.
- Allow the constructor of `SlackWebhookHandler` to pass a PSR-18 HTTP client.
- Use as default HTTP client an adapter of `php-http/guzzle6-adapter` instead of guzzle client.
- Added in `SlackLineFormatter` a third parameter to allow passing a custom format of the text.
- Removed from the default format of `SlackLineFormatter` the date since it is recorded on slack when the message is sent. 

## [v1.1.0] - 2018-11-12

### Changed
- Added version PHP 7.3 to travis.
- Require version 1.24 as minimum version of monolog. Unfortunately monolog introduced a BC on https://github.com/Seldaek/monolog/pull/808

> BC Notice: If you are extending any of the Monolog's Formatters' normalize method, make sure you add the new $depth = 0 argument to your function signature to avoid strict PHP warnings. See #808 for more details.

## [v1.0.0] - 2018-09-16

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
