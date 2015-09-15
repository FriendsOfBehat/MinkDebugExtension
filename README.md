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

Moreover, it comes bundled with some useful scripts, that will make uploading logs and screenshots extremely easy.

Installation
------------

Assuming you already have Composer:

```bash
composer require lakion/mink-debug-extension
```

Then you only need to configure your Behat profile:

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
  
Scripts
-------

MinkDebugExtension comes with three tiny, but powerful scripts:

#### Uploading textfiles

`vendor/lakion/mink-debug-extension/travis/tools/upload-textfiles "<glob_path>"` - uploades file contents to [termbin.com](http://termbin.com) and returns a list of file names and urls to uploaded content. 

Glob paths must be quoted to work correctly. Usage:
  
```bash
$ vendor/lakion/mink-debug-extension/travis/tools/upload-textfiles "logs/*.log"
$ vendor/lakion/mink-debug-extension/travis/tools/upload-textfiles README.md
```

#### Uploading screenshots
  
`vendor/lakion/mink-debug-extension/travis/tools/upload-screenshots "<glob_path>"` - uploades images to [imgur.com](http://imgur.com) and returns a list of file names and theirs urls. 

Glob paths must be quoted to work correctly. Requires Imgur API key to be set as environmental varialbe `$IMGUR_API_KEY`. Usage:

```bash
$ export IMGUR_API_KEY="imgur api key"

$ vendor/lakion/mink-debug-extension/travis/tools/upload-screenshots "logs/*.png"
$ vendor/lakion/mink-debug-extension/travis/tools/upload-screenshots favicon.ico
```

#### Waiting for port to be taken

`vendor/lakion/mink-debug-extension/travis/tools/wait-for-port <port> [limit = 15] [interval = 1]` - waits for service to appear at localhost at a given port, useful while waiting for Selenium or webserver to warm up.

Limit is the number of tries, interval is the delay in seconds between another tries. Usage:

```bash
$ java -jar selenium.jar &

$ vendor/lakion/mink-debug-extension/travis/tools/wait-for-port 4444
```
  
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

MinkDebugExtension was originally created by [Kamil Kokot](http://kamil.kokot.me).
See the list of [contributors](https://github.com/Lakion/MinkDebugExtension/contributors).
