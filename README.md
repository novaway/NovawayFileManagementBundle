# Deprecated

This project is no longer actively maintained.

# Novaway FileManagementBundle

[![Build Status](https://travis-ci.org/novaway/NovawayFileManagementBundle.svg)](https://travis-ci.org/novaway/NovawayFileManagementBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/05c5f0dc-60bb-4f9c-8ace-621777d754a4/mini.png)](https://insight.sensiolabs.com/projects/05c5f0dc-60bb-4f9c-8ace-621777d754a4)

### Compatibility

The current version of the bundle is only compatible with Symfony 2.5+
Version 1.0 provides compatibility with older versions of Symfony

### Summary and features
This package allows to manage files and image for entities in a smoother way with Symfony 2

### Usage
The documentation is stored in the `Resources/doc/index.md` file in this bundle.

[Read the Documentation for master](https://github.com/novaway/NovawayFileManagementBundle/blob/master/Resources/doc/index.md)

### Compiling the JavaScript files

Note: We already provide a compiled version of the JavaScript; this section is only
relevant if you want to make changes to this script.

In order to re-compile the JavaScript source files that we ship with this bundle, you
need the Google Closure Tools. You need the
[**plovr**](http://plovr.com/download.html) tool, it is a Java ARchive, so you
also need a working Java environment. You can re-compile the JavaScript with the
following command:

    $ java -jar plovr.jar build Resources/config/plovr/compile.js

Alternatively, you can use the JMSGoogleClosureBundle. If you install this bundle,
you can re-compile the JavaScript with the following command:

    $ php app/console plovr:build @NovawayFileManagementBundle/compile.js

### Testing
Unit tests are written using [atoum](https://github.com/atoum/atoum). You will get atoum, among other dependencies, when
running `composer install`. To run tests, you will need to run the following command :

``` sh
$ vendor/bin/atoum

# To run tests in a loop, ideal to do TDD
$ vendor/bin/atoum --loop
```

### Contributors:
- Cédric Spalvieri - Novaway [skwi69]
