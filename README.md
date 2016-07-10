The Jaxon core library
======================

Jaxon is an open source PHP library for easily creating Ajax web applications.
It allows into a web page to make direct Ajax calls to PHP classes that will in turn update its content, without reloading the entire page.

This package is the Jaxon core library.

Jaxon is a fork of the Xajax PHP library.

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

The project documentation is available in [English](http://www.jaxon-php.org/en/docs/) and [French](http://www.jaxon-php.org/fr/docs/).

Some sample codes are provided in the [jaxon-php/jaxon-examples](https://github.com/jaxon-php/jaxon-examples) package, and demonstrated on the website [here](http://www.jaxon-php.org/examples/).

Contribute
----------

- Issue Tracker: github.com/jaxon-php/jaxon-core/issues
- Source Code: github.com/jaxon-php/jaxon-core

License
-------

The project is licensed under the BSD license.
