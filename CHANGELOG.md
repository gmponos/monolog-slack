# Changelog

All Notable changes to `monolog-slack` will be documented in this file see this [url](http://keepachangelog.com/)

## [v0.2.0] - NEXT

### Changed
- Changed composer in order to make the package compatible with any version 6 of guzzle.
- `SlackFormatterInterface` now extends `FormatterInterface`
- [BC] Removed from constructor of `SlackWebhookHandler` the argument `$includeContextAndExtra`. Since this is package
is still at zero version we are allowed to do so.

## [v0.1.0] - 2018-03-24

### Added
- Created the initial functionality.
