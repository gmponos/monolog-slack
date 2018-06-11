# Monolog-Slack

## Description

This is a package that can help you send logs through monolog to slack using webhooks.
Monolog already has a Slack handler but I am not in favor of it for two reasons

**It has some bugs**

- It does not normalize correctly objects [see](https://github.com/Seldaek/monolog/pull/1127)
- Slack accepts until 2000 chars for [see](https://github.com/Seldaek/monolog/issues/909)

**Performance**

- Although Monolog has the `WhatFailureGroupHandler` I would consider it simpler not to wrap my handler around another
 handler and have a simpler and faster logic [see](https://github.com/Seldaek/monolog/issues/920)
- SlackWebhooHandler does not have timeouts and it executes retries if Slack service is down [see](https://github.com/Seldaek/monolog/pull/846#issuecomment-373522968)

**Formatting**

- Current package gives you the ability to add a custom formatter to the handler in order to format Attachments.
Although you can pass a formatter to Slack Handlers of monolog they are not executed for Attachments.
- I liked the formatting that I created better.
 
## Install

You can install this package through composer

```
$ composer require webthink/monolog-slack
```

## Documentation

    @todo

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing
Assuming you have PHPUnit installed system wide using one of the methods stated
[here](http://phpunit.de/manual/current/en/installation.html), you can run the
tests for this package by doing the following:

1. Copy `phpunit.xml.dist` to `phpunit.xml`.
2. Run `phpunit` from bash.
