The Jaxon core library
======================

Jaxon is an open source PHP class library for easily creating powerful PHP-driven, web-based Ajax applications.
Using Jaxon, you can asynchronously call PHP functions and update the content of your webpage without reloading the page.

The Jaxon library was recently updated and modernized to make the most of the latest features of the PHP language.

This package is the Jaxon core library.

What has changed?
-----------------

- The Jaxon PHP library is modular, and consists of a core package and several plugin packages.
- The javascript library is provided in a separated and javascript-only package.
- All PHP packages install with `Composer`, are fully namespaced, and implement `PSR-4` autoloading.
- The Jaxon library runs on PHP versions 5.4 to 7.0.

New features
------------

- All the Jaxon classes in a directory can be registered in two lines of code, possibly with a namespace.
- The name of javascript classes takes into account their namespace and the subdirectory where they are located.
- The Jaxon library can load its configuration settings from a file. Supported formats are JSON, YAML and PHP.
- The Jaxon library provides a pagination feature out of the box.

Documentation
------------

The project documentation is available in [English](http://jaxon.lagdo-software.net/docs/en/) and [French](http://jaxon.lagdo-software.net/docs/fr/).

Some sample codes are provided in the [lagdo/jaxon-examples](https://github.com/lagdo/jaxon-examples) package, which is installed on a demo server [here](http://jaxon.lagdo-software.net/).

Contribute
----------

- Issue Tracker: github.com/lagdo/jaxon-core/issues
- Source Code: github.com/lagdo/jaxon-core

License
-------

The project is licensed under the BSD license.
