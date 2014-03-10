RollerworksMultiUserBundle
==========================

[![Build Status](https://travis-ci.org/rollerworks/RollerworksMultiUserBundle.png?branch=master)](https://travis-ci.org/rollerworks/RollerworksMultiUserBundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/rollerworks/RollerworksMultiUserBundle/badges/quality-score.png?s=d98ca957ce5deb8b4bd41532cae263b8a1639121)](https://scrutinizer-ci.com/g/rollerworks/RollerworksMultiUserBundle/)
[![Code Coverage](https://scrutinizer-ci.com/g/rollerworks/RollerworksMultiUserBundle/badges/coverage.png?s=7c6bc63ae39599e5af8326c848dc62759a290c6e)](https://scrutinizer-ci.com/g/rollerworks/RollerworksMultiUserBundle/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9a47cef8-7640-4f20-9efe-0153325d66ba/mini.png)](https://insight.sensiolabs.com/projects/9a47cef8-7640-4f20-9efe-0153325d66ba)


The RollerworksMultiUserBundle adds support for a multi-user set-up using the FOSUserBundle.
It provides a fully compatible in-place replacement for the 'fos_user' services.

In practice it is build on-top of the FOSUserBundle, and uses the original controllers, forms and UserManager.

Features include:

- Fully compatible with existing bundles that already use the FOSUserBundle
- Unlimited user-systems, each with there own configuration, storage engine, templates, forms, etc.
- Easy generation of new user-systems
- Unit tested

**Caution:** This bundle is developed in sync with [FOSUserBundle's repository](https://github.com/FriendsOfSymfony/FOSUserBundle).
For FOSUserBundle 2.0.x, you need to use the 1.0 release of the bundle (or lower).

**Warning:** This bundle can not be used in combination with the PUGXMultiUserBundle.
If the PUGXMultiUserBundle is installed, then please remove it before continuing.

Documentation
-------------

The bulk of the documentation is stored in the `doc/index.md`
file in this bundle:

[Read the Documentation for master](https://github.com/rollerworks/RollerworksMultiUserBundle/blob/master/docs/index.md)

Installation
------------

All the installation instructions are located in [documentation](https://github.com/rollerworks/RollerworksMultiUserBundle/blob/master/docs/index.md).

License
-------

This bundle is released under the MIT license.
See the bundled LICENSE file for details.

About
-----

RollerworksMultiUserBundle was designed as an alternative to the PUGXMultiUserBundle.

A major difference to the PUGXMultiUserBundle is that RollerworksMultiUserBundle
does not use Doctrine ORM Joined-Entity inheritance and provides a richer set of features.

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/Rollerworks/RollerworksMultiUserBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.

Credits
-------

The original idea of the UserDiscriminator came from the PUGXMultiUserBundle.

A major difference to the PUGXMultiUserBundle is as that RollerworksMultiUserBundle
does not use Doctrine ORM Joined-Entity inheritance and every user-manager is accessible without 'discriminating'.

This bundle contains source originally designed by the FOSUserBundle developers.

Running the Tests
-------

Before running the tests, you will need to install the bundle dependencies. Do this using composer:

> The doctrine/mongodb-odm is required for functional tests
> but are not installed by default as it fails with some of the automated code analyzers.

``` bash
$ php composer.phar composer require doctrine/mongodb-odm:"1.0.*@dev" --no-update
$ php composer.phar composer require doctrine/mongodb-odm-bundle:"3.0.*@dev" --no-update
$ php composer.phar --dev install
```

Then you can launch phpunit (make sure its installed https://github.com/sebastianbergmann/phpunit/#installation)

> Using the Composer version of PHPUnit currently fails so make sure to either use the Phar archive or PEAR version.
> You need at least version 3.6 of PHPUnit and MockObject plugin 1.0.8

``` bash
$ phpunit -c phpunit.xml.dist
```

**Note:** Functional test are not run by default, to run all tests make sure both
PDO_SQLite and the PHP extension for MongoDB are installed, and launch phpunit with:

``` bash
$ bin/phpunit -c phpunit.xml.dist --exclude-group ""
```

> Optionally you skip the functional tests as these are always run automatically on Travis-CI
> when opening a Pull Request.
