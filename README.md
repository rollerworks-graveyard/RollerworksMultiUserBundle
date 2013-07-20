RollerworksMultiUserBundle
==========================

The RollerworksMultiUserBundle adds support for a multi-user set-up using the FOSUserBundle.
It provides a fully compatible in-place replacement for the 'fos_user' services.

In practice it is build on-top of the FOSUserBundle, and uses the original controllers, forms and UserManager.

Features include:

- Fully compatible with existing bundles that already use the FOSUserBundle
- Unlimited user-systems, each with there own configuration, storage engine, templates, forms, etc.
- Unit tested

**Caution:** This bundle is developed in sync with [FOSUserBundle's repository](https://github.com/FriendsOfSymfony/FOSUserBundle).
For FOSUserBundle 2.0.x, you need to use the 1.0 release of the bundle (or lower).

**Warning:** This bundle can not be used in combination with the PUGXMultiUserBundle.
If the PUGXMultiUserBundle is installed, then please remove it before continuing.

Documentation
-------------

The bulk of the documentation is stored in the `doc/index.md`
file in this bundle:

[Read the Documentation for master](https://github.com/Rollerworks/RollerworksMultiUserBundle/blob/master/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](https://github.com/Rollerworks/RollerworksMultiUserBundle/blob/master/doc/index.md).

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

