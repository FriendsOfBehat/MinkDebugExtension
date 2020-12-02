MinkDebugExtension
==================

**MinkDebugExtension** is a Behat extension made for debugging and logging Mink related data after every failed step. 
It is especially useful while running tests on continuous integration server like Travis.
While using appropriate driver, you can also save screenshots just after the failure.

Installation
------------

Assuming you already have Composer:

```bash
composer require friends-of-behat/mink-debug-extension
```

Then you only need to configure your Behat profile:

```yml
default:
    extensions:
        FriendsOfBehat\MinkDebugExtension:
            directory: directory-where-to-save-logs
```

Configuration reference
-----------------------

Under `FriendsOfBehat\MinkDebugExtension` there are three options to be configured:

  - `directory` (required to enable extension) - contains path to directory that will contain generated logs. Use the variable `%paths.base%` to refer to the directory where your `behat.yml` is
  - `screenshot` (default `false`) - whether to save screenshots if using supporting driver
  - `clean_start` (default `true`) - whether to clean your existing logs on each Behat execution
  
Testing
-------

In order to test the extensions run:

```bash
composer install
bin/behat --strict
```

Authors
-------

MinkDebugExtension was originally created by [Kamil Kokot](https://kamilkokot.com).
See the list of [contributors](https://github.com/FriendsOfBehat/MinkDebugExtension/contributors).
