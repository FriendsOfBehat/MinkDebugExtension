MinkDebugExtension
==================

[![License](https://img.shields.io/packagist/l/lakion/mink-debug-extension.svg)](https://packagist.org/packages/lakion/mink-debug-extension)
[![Version](https://img.shields.io/packagist/v/lakion/mink-debug-extension.svg)](https://packagist.org/packages/lakion/mink-debug-extension)
[![Total Downloads](https://img.shields.io/packagist/dt/lakion/mink-debug-extension.svg)](https://packagist.org/packages/lakion/mink-debug-extension)
[![Build status...](https://img.shields.io/travis/Lakion/MinkDebugExtension/master.svg)](http://travis-ci.org/Lakion/MinkDebugExtension)
[![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/Lakion/MinkDebugExtension.svg)](https://scrutinizer-ci.com/g/Lakion/MinkDebugExtension/)

**MinkDebugExtension** is a Behat extension made for debugging and logging Mink related data after every failed step. 
It is especially useful while running tests on continuous integration server like Travis.
While using Selenium2 driver, you can also save screenshots just after the failure.

Installation
------------

Assuming you already have Composer:

```bash
composer require lakion/mink-debug-extension
```

Then you only need to configure you Behat profile:

```yml
default:
    extensions:
        Lakion\Behat\MinkDebugExtension:
            directory: directory-where-to-save-logs
```

Configuration reference
-----------------------

Under `Lakion\Behat\MinkDebugExtension` there are three options to be configured:

  - `directory` (required to enable extension) - contains path to directory that will contain generated logs, it can be relative to directory where your `behat.yml` is
  - `screenshot` (default `false`) - whether to save screenshots if using `Selenium2Driver`
  - `clean_start` (default `true`) - whether to clean your existing logs on each Behat execution
  
Testing
-------

In order to test the extensions run:

```bash
composer install
bin/phpspec run
bin/behat --strict
```

Authors
-------

Sylius was originally created by [Kamil Kokot](http://kamil.kokot.me).
See the list of [contributors](https://github.com/Lakion/MinkDebugExtension/contributors).
