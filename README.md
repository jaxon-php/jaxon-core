[![Build Status](https://travis-ci.org/jaxon-php/jaxon-core.svg?branch=master)](https://travis-ci.org/jaxon-php/jaxon-core)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jaxon-php/jaxon-core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jaxon-php/jaxon-core/?branch=master)
[![StyleCI](https://styleci.io/repos/60390067/shield?branch=master)](https://styleci.io/repos/60390067)

[![Latest Stable Version](https://poser.pugx.org/jaxon-php/jaxon-core/v/stable)](https://packagist.org/packages/jaxon-php/jaxon-core)
[![Total Downloads](https://poser.pugx.org/jaxon-php/jaxon-core/downloads)](https://packagist.org/packages/jaxon-php/jaxon-core)
[![Latest Unstable Version](https://poser.pugx.org/jaxon-php/jaxon-core/v/unstable)](https://packagist.org/packages/jaxon-php/jaxon-core)
[![License](https://poser.pugx.org/jaxon-php/jaxon-core/license)](https://packagist.org/packages/jaxon-php/jaxon-core)

The Jaxon core library
======================

Jaxon is an open source PHP library for easily creating Ajax web applications.
It allows into a web page to make direct Ajax calls to PHP classes that will in turn update its content, without reloading the entire page.

Jaxon is a fork of the Xajax PHP library.

This package is the Jaxon core library. Several plugins are provided in separate packages.

Features
--------

- All the Jaxon classes in a directory can be registered in one shot, possibly with a namespace.
- The configuration settings can be loaded from a file. Supported formats are JSON, YAML and PHP.
- The library provides pagination feature out of the box.
- The library is modular, and consists of a core package and several plugin packages.
- The javascript library is provided in a separated and javascript-only package, loaded by default from the [jsDelivr CDN](https://www.jsdelivr.com/projects/jaxon).
- The generated javascript classes are named according to their namespace or the subdirectory where they are located.
- All PHP packages install with `Composer`, are fully namespaced, and implement `PSR-4` autoloading.
- Starting from release 2.1, the library natively implements Ajax file upload.
- The Jaxon library runs on PHP versions 5.4 to 7.0.

Documentation
-------------

The project documentation is available in [English](http://www.jaxon-php.org/en/docs/) and [French](http://www.jaxon-php.org/fr/docs/).

Some sample codes are provided in the [jaxon-php/jaxon-examples](https://github.com/jaxon-php/jaxon-examples) package, and demonstrated in [the website](http://www.jaxon-php.org/examples/).

Contribute
----------

- Issue Tracker: github.com/jaxon-php/jaxon-core/issues
- Source Code: github.com/jaxon-php/jaxon-core

License
-------

The project is licensed under the BSD license.
