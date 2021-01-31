Gravity Flow Form Connector Extension
=====================================

[![PHPUnit Tests](https://github.com/gravityflow/gravityflowformconnector/workflows/PHPUnit%20Tests/badge.svg)](https://github.com/gravityflow/gravityflowformconnector/actions?query=workflow%3A%22PHPUnit+Tests%22)  [![CircleCI](https://circleci.com/gh/gravityflow/gravityflowformconnector.svg?style=svg)](https://circleci.com/gh/gravityflow/gravityflowformconnector)

The Gravity Flow Form Connector Extension is a premium plugin for WordPress which allows Gravity Flow administrators to create workflow steps that create or update entries for a different form.

This repository is a development version of the Gravity Flow Form Connector Extension and is intended to facilitate communication with developers. It is not stable and not intended for installation on production sites.

Bug reports and pull requests are welcome.

If you'd like to receive the release version, automatic updates and support please purchase a license: https://gravityflow.io.


## Installation Instructions
The only thing you need to do to get this development version working is clone this repository into your plugins directory and activate script debug mode. If you try to use this plugin without script mode on the scripts and styles will not load and it will not work properly.

To enable script debug mode just add the following line to your wp-config.php file:

define( 'SCRIPT_DEBUG', true );

## Support
If you'd like to receive the stable release version, automatic updates and support please purchase a license here: https://gravityflow.io. 

We cannot provide support to anyone without a valid license.

## Test Suites

The integration tests can be installed from the terminal using:

    bash tests/bin/install.sh [DB_NAME] [DB_USER] [DB_PASSWORD] [DB_HOST]


If you're using VVV you can use this command:

	bash tests/bin/install.sh wordpress_unit_tests root root localhost

The acceptance tests are completely separate from the unit tests and do not require the unit tests to be configured. Steps to install and configure the acceptance tests:
 
1. Install the dependencies: `composer install`
2. Download and start either PhantomJS or Selenium.
3. Copy codeception-sample-vvv.yml or codeception-sample-pressmatic.yml to codeception.yml and adjust it to point to your test site. Warning: the database will cleaned before each run.
4. Run the tests: `./vendor/bin/codecept run`

## Documentation
User Guides, FAQ, Walkthroughs and Developer Docs: http://docs.gravityflow.io

Class documentation: http://codex.gravityflow.io

## Translations
If you'd like to translate the Gravity Flow Form Connector Extension into your language please create a free account here:

https://www.transifex.com/projects/p/gravityflow/

## Legal
Gravity Flow is a legally registered trademark belonging to Steven Henty S.L. Further details: https://gravityflow.io/trademark

Copyright 2015-2018 Steven Henty. All rights reserved.
