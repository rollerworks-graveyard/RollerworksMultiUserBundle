Getting Started With RollerworksMultiUserBundle
===============================================

The FOSUserBundle a powerful user management solution, but can only support one user type per installation.
RollerworksMultiUserBundle provides a framework on top of the FOSUserBundle to support
multiple users at the same time.

Because its build on-top of the FOSUserBundle, you don't have to change existing bundles to use this bundle.

## Prerequisites

You need at least Symfony 2.3, FOSUserBundle 2.0 (2.0.0-alpha1) and the Composer package manager.

Most of the configuration and creating of classes is kept in sync with the FOSUserBundle,
so if something is not described in detail here you can read the FOSUserBundle documentation as reference.

The original commands can be used as normal, but require you also include the '--user-system' parameter,
to indicate which user-system must be used.

*The user-system name is the first parameter you pass to UserServicesFactory::create()*

```bash
php app/console fos:user:create --user-system=acme_user matthieu
```

**Note:** Each user-system is required to have its own Form types to functional properly,
you can not reuse the form types of UserA for UserB. If you don't specify anyone explicit
the system will register the user-system form-types for you.

## Working

The system works with a `UserDiscriminator`-service which determines which user-system should be handled,
and delegates all handling to actual user-services.

**Note:** The original fos_user service definitions and configuration are overwritten and automatically
configured for multi user support.

**Caution:** Configuration for the FOSUserBundle is handled trough the RollerworksMultiUserBundle,
you must not set the `fos_user` (or remove it if you have it configured) configuration in your app/config,
use `rollerworks_multi_user` instead. Setting the fos_user configuration yourself **will**
break the RollerworksMultiUserBundle.

> Finding the correct user is done using the AuthenticationListener and RequestListener services.
> You can also choose to build your own discriminator service, just be careful.

A user-system is also referred to as a 'user-bundle'.

## Installation

**Note:**

> The RollerworksMultiUserBundle allows you to create as many user-systems as you need,
> just repeat all the steps described in section 3 for each system you want to create.
>
> But. Remember that each user-system must have its own: name, firewall and request-matcher
> to not cause any conflict.

1. Download RollerworksMultiUserBundle
2. Enable the Bundle
3. Create your UserSystem(s)
4. Configure your application's security.yml
5. Configure the RollerworksMultiUserBundle
6. Using the User Manager
7. Import routing
8. Update your database schema

### 1: Download RollerworksMultiUserBundle using composer

Add RollerworksMultiUserBundle in your composer.json:

```
{
    "require": {
        "rollerworks/multi-user-bundle": "1.0.*@dev"
    }
}

```

Now, run the composer to download the bundle:

``` bash
$ php composer.phar update rollerworks/multi-user-bundle
```

Composer will install the bundle to your project's `vendor/rollerworks` directory.

### Step 2: Enable the bundle

**Caution:** Make sure FOSUserBundle always comes before the RollerworksMultiUserBundle.

**Note:** If you want to use the SonataUserBundle make sure to NOT set the FOSUserBundle as parent,
every bundle can only have one parent.

Enable the bundles in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(),
        new Rollerworks\Bundle\MultiUserBundle\RollerworksMultiUserBundle(),
    );
}
```

### 3: Create your UserSystem(s)

To create your new user-system, you can either create everything by hand or use
the `rollerworks:multi-user:generate:usersys` command to get the skeleton generated for you.

**Note:**

> Only the classes are generated, you need create the Mapping data for db-driver yourself.

> In the example all routing path's will be prefixed with 'user/' for explicitness,
> you can decide not to use them or use your own.

To create your new user-system, first create a new bundle skeleton.

**Note:**

> Using this method requires the SensioGeneratorBundle to be installed and enabled,
> which is the case for the symfony-standard edition.
>
> When using the generate command you can skip to [3.5.](index.md#35-creating-routing-files)

``` bash
$ php app/console rollerworks:multi-user:generate:usersys
```

See: [Generating a New Bundle Skeleton](http://symfony.com/doc/current/bundles/SensioGeneratorBundle/commands/generate_bundle.html)
    for more details on creating a new bundle skeleton.

For this example we'll be using `AcmeUserBundle` as our bundle name,
which will be planed in the `Acme\UserBundle` namespace with YAML as configuration format.

#### 3.1: Create your User class

See [FOSUserBundle - Create your User class](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md#step-3-create-your-user-class) for full details.

**Caution:** The Doctrine mappings of the FOSUserBundle may not be enabled properly, make sure you either use
the `rollerworks:multi-user:generate:usersys` command or load them manually for each model-manager that is used
by the configured user-systems.

```yaml
doctrine:
    orm:
        default_entity_manager: default
        entity_managers:
            default:
                connection: default
                mappings:
                    FOSUserBundle:
                        type:        xml
                        dir:         '%kernel.root_dir%/../vendor/friendsofsymfony/user-bundle/Resources/config/doctrine/model'
                        prefix:      FOS\UserBundle\Model
                        is_bundle:   false
```

**For other Doctrine drivers please refer to official documentation.**

#### 3.2: Register the user-system

**Note.** Activating the new user-system requires it to be enabled in the AppKernel.

To make registering all services for the user-system as simple as possible,
the RollerworksMultiUserBundle comes with a handy Dependency Injector helper
named the `UserServicesFactory`. Which will register all the internal services for you.

`UserServicesFactory::create()` will register a new user-system in the `ContainerBuilder` for you.

And the `UserServicesFactory::create()` will also register the form-types you need,
any form-type/name/class that belongs to the FOSUserBundle will be converted to an ready
to use form-type. Remember form types and names will start the service-prefix of the user-system.

```
'form' => array(
    'type' => 'fos_user_profile',
    'class' => 'FOS\UserBundle\Form\Type\ProfileFormType',
    'name' => 'fos_user_profile_form',
),
```

Will internally get converted to an 'acme_user_profile' form-type service definition.
Using the `Rollerworks\Bundle\MultiUserBundle\Form\Type\ProfileFormType` as class for
the service definition.

But of course you can also use your own form classes, see [Overriding Forms](overriding_forms.md)
for more information.

**Note:**

> You should not extend from the `Rollerworks\Bundle\MultiUserBundle\Form\Type\\` namespace.
> These form classes are only meant to be used and registered by the RollerworksMultiUserBundle.

The first parameter is the name of the user-system, the second is the configuration which internally
is normalized by the Symfony Config component. In the next section you will learn how you can use this
to make your user-bundle more configurable.

> You can also choose to add more then one user-system in your bundle,
> but from a separation perspective its better to only have one user-system per bundle.

```php
<?php
// src/Acme/UserBundle/DependencyInjection/AcmeUserExtension.php

namespace Acme\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;

class AcmeUserExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $factory = new UserServicesFactory($container);

        $factory->create('acme_user', array(
            array(
                'path' => '^/user',
                'host' => null,
                'request_matcher' => null,

                // When not set these will inherit from the user-system name provided above
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'db_driver' => 'orm', // can be either: orm, mongodb, couchdb or custom (Propel is not supported)
                'model_manager_name' => 'default',

                // When not set these will inherit from the system wide configuration
                'from_email' => array(
                    'address' => 'info@example.com',
                    'sender_name' => 'webmaster',
                ),

                'user_class' => 'Acme\UserBundle\Entity\User',
                'firewall_name' => 'user', // this must equal to the firewall-name used for this section

                // Optional can be empty
                'group' => array(
                    'group_class' => 'Acme\UserBundle\Entity\Group'
                ),

                // Optional can be empty
                'profile' => array(
                    'template' => array(
                        'edit' => 'AcmeUserBundle:Profile:edit.html.twig',
                        'show' => 'AcmeUserBundle:Profile:show.html.twig',
                    ),
                    'form' => array(
                        'type' => 'fos_user_profile',
                        'class' => 'FOS\UserBundle\Form\Type\ProfileFormType',
                        'name' => 'fos_user_profile_form',
                    ),
                ),

                // Optional can be empty
                'registration' => array(
                    'template' => array(
                        'register' => 'AcmeUserBundle:Registration:register.html.twig',
                        'check_email' => 'AcmeUserBundle:Registration:checkEmail.html.twig',
                    ),
                    'form' => array(
                        'type' => 'fos_user_registration',
                        'class' => 'FOS\UserBundle\Form\Type\RegistrationFormType',
                        'name' => 'fos_user_registration_form',
                    ),
                ),

                // Optional can be empty
                'resetting' => array(
                    'template' => array(
                        'check_email' => 'AcmeUserBundle:Resetting:checkEmail.html.twig',
                        'email' => 'AcmeUserBundle:Resetting:email.txt.twig',
                        'password_already_requested' => 'AcmeUserBundle:Resetting:passwordAlreadyRequested.html.twig',
                        'request' => 'AcmeUserBundle:Resetting:request.html.twig',
                        'reset' => 'AcmeUserBundle:Resetting:reset.html.twig',
                    )
                ),

                // Optional can be empty
                'change_password' => array(
                    'template' => array(
                        'change_password' => 'AcmeUserBundle:ChangePassword:changePassword.html.twig',
                    )
                ),
            )
        ));
    }
}
```

**Note:**

> `path` and `host` are used by the `RequestListener` service for finding the correct user-type.
> You can either set only a path or host, or both.
> Or you can choose to use your own matcher-service by setting the `request_matcher`, and giving it the service-id.
>
> A request-matcher must always implement the `Symfony\Component\HttpFoundation\RequestMatcherInterface`.

**Caution:** Never set the template namespace to FOSUserBundle or RollerworksMultiUserBundle as this create a loop-back!

For more details on the configuration reference see
[FOSUserBundle Configuration Reference](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/configuration_reference.md)
for original description.

#### 3.3: Make your bundle configurable

As you'd properly don't want to hard-code the configuration of your user-bundle,
you can use the following to make your bundle more configurable.

```php

<?php
// src/Acme/UserBundle/DependencyInjection/AcmeUserExtension.php

namespace Acme\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;

class AcmeUserExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $factory = new UserServicesFactory($container);
        $factory->create('acme_user', array(
            array(
                /*
                 * `path` and `host` are used by the `RequestListener` service for finding the correct user-type.
                 * You can either set only a path or host, or both. Or you can choose to use your own matcher-service
                 * by setting the `request_matcher`, and giving it the service-id.
                 *
                 * A request-matcher must always implement the `Symfony\Component\HttpFoundation\RequestMatcherInterface`.
                 */

                'path' => '^/user', // path-regex, must match the firewall pattern
                'host' => null,
                'request_matcher' => null,

                // When not set these will inherit from the user-system name provided above
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'db_driver' => 'orm', // can be either: orm, mongodb, couchdb or custom (Propel is not supported)
                'model_manager_name' => 'default',
                'use_listener' => true,

                'user_class' => 'Acme\UserBundle\Entity\\User',
                'firewall_name' => 'main', // this must equal to the firewall-name used for this user-system

                'use_username_form_type' => true,

                // When not set this will inherit from the system wide configuration
                'from_email' => array(
                    'address' => null,
                    'sender_name' => null,
                ),

                'security' => array(
                    'login' => array(
                        'template' => 'RollerworksMultiUserBundle:UserBundle/Security:login.html.twig',
                    ),
                ),

                'service' => array(
                    'mailer' => 'fos_user.mailer.default',
                    'email_canonicalizer' => 'fos_user.util.canonicalizer.default',
                    'username_canonicalizer' => 'fos_user.util.canonicalizer.default',
                    'user_manager' => 'fos_user.user_manager.default',
                ),

                'template' => array(
                    'layout' => 'RollerworksMultiUserBundle::layout.html.twig',
                ),

                'profile' => array(
                    'form' => array(
                        'class' => 'FOS\\UserBundle\\Form\\Type\\ProfileFormType',
                        'type' => 'fos_user_profile',
                        'name' => 'fos_user_profile_form',
                        'validation_groups' => array('Profile', 'Default'),
                    ),
                    'template' => array(
                        'edit' => 'RollerworksMultiUserBundle:UserBundle/Profile:edit.html.twig',
                        'show' => 'RollerworksMultiUserBundle:UserBundle/Profile:show.html.twig',
                    ),
                ),

                'change_password' => array(
                    'form' => array(
                        'class' => 'FOS\\UserBundle\\Form\\Type\\ChangePasswordFormType',
                        'type' => 'fos_user_change_password',
                        'name' => 'fos_user_change_password_form',
                        'validation_groups' => array('ChangePassword', 'Default'),
                    ),
                    'template' => array(
                        'change_password' => 'RollerworksMultiUserBundle:UserBundle/ChangePassword:changePassword.html.twig',
                    ),
                ),

                'registration' => array(
                    'confirmation' => array(
                        'enabled' => false,
                        'template' => array(
                            'email' => 'RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig',
                            'confirmed' => 'RollerworksMultiUserBundle:UserBundle/Registration:confirmed.html.twig',
                        ),
                        'from_email' => array(
                            'address' => null,
                            'sender_name' => null,
                        ),
                    ),
                    'form' => array(
                        'class' => 'FOS\\UserBundle\\Form\\Type\\RegistrationFormType',
                        'type' => 'fos_user_registration',
                        'name' => 'fos_user_registration_form',
                        'validation_groups' => array('Registration', 'Default'),
                    ),
                    'template' => array(
                        'register' => 'RollerworksMultiUserBundle:UserBundle/Registration:register.html.twig',
                        'check_email' => 'RollerworksMultiUserBundle:UserBundle/Registration:checkEmail.html.twig',
                    ),
                ),

                'resetting' => array(
                    'token_ttl' => 86400,
                    'email' => array(
                        'from_email' => array(
                            'address' => null,
                            'sender_name' => null,
                        ),
                    ),
                    'form' => array(
                        'template' => null,
                        'class' => 'FOS\\UserBundle\\Form\\Type\\ResettingFormType',
                        'type' => 'fos_user_resetting',
                        'name' => 'fos_user_resetting_form',
                        'validation_groups' => array('ResetPassword', 'Default'),
                    ),
                    'template' => array(
                        'check_email' => 'RollerworksMultiUserBundle:UserBundle/Resetting:checkEmail.html.twig',
                        'email' => 'RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig',
                        'password_already_requested' => 'RollerworksMultiUserBundle:UserBundle/Resetting:passwordAlreadyRequested.html.twig',
                        'request' => 'RollerworksMultiUserBundle:UserBundle/Resetting:request.html.twig',
                        'reset' => 'RollerworksMultiUserBundle:UserBundle/Resetting:reset.html.twig',
                    ),
                ),

                // Optional
                /*
                'group' => array(
                    'group_class' => 'Acme\UserBundle\Entity\\Group',
                    'group_manager' => 'fos_user.group_manager.default',

                    'form' => array(
                        'class' => 'FOS\\UserBundle\\Form\\Type\\GroupFormType',
                        'type' => 'fos_user_group',
                        'name' => 'fos_user_group_form',
                        'validation_groups' => array('Registration', 'Default'),
                    ),
                    'template' => array(
                        'edit' => 'RollerworksMultiUserBundle:UserBundle/Group:edit.html.twig',
                        'list' => 'RollerworksMultiUserBundle:UserBundle/Group:list.html.twig',
                        'new' => 'RollerworksMultiUserBundle:UserBundle/Group:new.html.twig',
                        'show' => 'RollerworksMultiUserBundle:UserBundle/Group:show.html.twig',
                    ),
                ),
                */


            ),
            // Pas the 'normalized' application config, this will get merged later
            $config
        ));
    }
}
```

```php
<?php
// src/Acme/UserBundle/DependencyInjection/Configuration.php

namespace Acme\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration as UserConfiguration;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acme_user');

        // ...

        $configuration = new UserConfiguration();

        // Now add the configuration tree to the 'acme_user' configuration-root
        $configuration->addUserConfig($rootNode);

        return $treeBuilder;
    }
```

Now you can now easily configure the user-bundle in your `app/config/config.yml`
config using the `acme_user` configuration-tree.

You can limit what is configurable by passing a bitmask as second parameter to `UserConfiguration::addUserConfig()`

Available constants of the `Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration` class are:

* CONFIG_SECTION_PROFILE
* CONFIG_SECTION_CHANGE_PASSWORD
* CONFIG_SECTION_REGISTRATION
* CONFIG_SECTION_RESETTING
* CONFIG_SECTION_GROUP
* CONFIG_SECTION_SECURITY

* CONFIG_DB_DRIVER
* CONFIG_REQUEST_MATCHER
* CONFIG_USER_CLASS

* CONFIG_ALL

To enable everything (which is the default) use `Configuration::CONFIG_ALL`.

To remove what is configurable, use the `^` operator, `Configuration::CONFIG_ALL ^ Configuration::CONFIG_SECTION_GROUP`
will allow everything but the Group configuration.

The other way around is to define explicitly with `|`.

`Configuration::CONFIG_SECTION_CHANGE_PASSWORD | Configuration::CONFIG_SECTION_PROFILE`

Will only enable the change-password and profile configuration, and nothing more.

**Note:** The `UserServicesFactory::create()` ignores any unknown configuration-keys that are passed to it, so you don't
have to worry about passing to much.

##### Placing the configuration under its own level.

If you don't want to add the user-configuration at root level, use the following instead.

```php

<?php
// src/Acme/UserBundle/DependencyInjection/Configuration.php

namespace Acme\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration as UserConfiguration;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $userNode = $builder->root('user');
        $userConfig = new UserConfiguration();

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acme_user');

        // This will add the configuration to the user node, which then in turn is added to the acme_user root-node
        $rootNode->append($userConfig->addUserConfig($userNode));

        // ...

        return $treeBuilder;
    }
}
```

See also: [Defining and processing configuration values - Appending sections](http://symfony.com/doc/current/components/config/definition.html#appending-sections)

#### 3.4: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(),
        new Rollerworks\Bundle\MultiUserBundle\RollerworksMultiUserBundle(),
        new Acme\UserBundle\AcmeUserBundle(),
    );
}
```

And if bundle configuration is enabled (see previous sub-section: 3.3).

```yaml
# app/config/config.yml

acme_user:
    path: "^/user"
```

#### 3.5: Creating routing files

Now that you have activated and configured the bundle, all that is left to do is
creating and importing the routing files.

**Note:**

> Make sure the route-name prefix is the same as configured in the user-system.
> In practice this means replacing the `fos_user` prefix with `acme_user`.
>
> You can skip the copying of the routing files when you used the generate command.
> Depending on your choose during the generating, your file-prefix may vary.

First copy the `Resources/config/routing` from the FOSUserBundle to your user-bundle.

Now replace all the `fos_user` route prefixes to `acme_user` or what you have configured for your bundle.

By importing the routing files you will have ready made pages for things such as
log-in, creating users, etc.

**Caution:**

> When you change the routing prefix also remember to update your
> firewall pattern and user-system request-matching configuration.

In YAML:

``` yaml
# app/config/routing.yml
acme_user_security:
    resource: "@AcmeUserBundle/Resources/config/routing/security.yml"
    prefix: /user/profile

acme_user_profile:
    resource: "@AcmeUserBundle/Resources/config/routing/profile.yml"
    prefix: /user/profile

acme_user_register:
    resource: "@AcmeUserBundle/Resources/config/routing/registration.yml"
    prefix: /user/register

acme_user_resetting:
    resource: "@AcmeUserBundle/Resources/config/routing/resetting.yml"
    prefix: /user/resetting

acme_user_change_password:
    resource: "@AcmeUserBundle/Resources/config/routing/change_password.yml"
    prefix: /user/profile
```

Or if you prefer XML:

``` xml
<!-- app/config/routing.xml -->
<import resource="@AcmeUserBundle/Resources/config/routing/security.xml"/>
<import resource="@AcmeUserBundle/Resources/config/routing/profile.xml" prefix="/profile" />
<import resource="@AcmeUserBundle/Resources/config/routing/registration.xml" prefix="/register" />
<import resource="@AcmeUserBundle/Resources/config/routing/resetting.xml" prefix="/resetting" />
<import resource="@AcmeUserBundle/Resources/config/routing/change_password.xml" prefix="/profile" />
```

**Note:**

> In order to use the built-in email functionality (confirmation of the account,
> resetting of the password), you must activate and configure the SwiftmailerBundle.
> Or configure your own mailer service.

### 4: Configure your application's security.yml

In order for the Symfony security component to use the user-system, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic security configuration for your application is contained.

Below is a minimal example of the configuration necessary to use your user-bundle
in your application:

``` yaml
# app/config/security.yml
security:
    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN

    providers:
        acme_user_bundle:
            id: acme_user.user_provider.username

    firewalls:
        main:
            # Pattern should equal the pattern you configured for the bundle
            pattern: ^/user
            # Or if you use a custom request_matcher configure that one instead
            #request_matcher: some.service.id

            form_login:
                provider: acme_user_bundle
                csrf_provider: form.csrf_provider
            logout:       true
            anonymous:    true

    access_control:
        - { path: ^/user/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/user/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/user/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/, role: ROLE_ADMIN }
```

The `encoders` section defines the used encoding for the user-passwords,
this example assumes you always the same encoder for all users.

To define an encoder per user, change the `FOS\UserBundle\Model\UserInterface` with the fully
qualified class name of the user-class. `Acme/UserBundle/Model/User` for example.

See also: [Encoding the User's Password](http://symfony.com/doc/current/book/security.html#encoding-the-user-s-password)

Under the `providers` section, you are making the bundle's packaged user provider
service available via the alias `acme_user_bundle`. The id of the bundle's user
provider service is `acme_user.user_provider.username`.

> Remember that in section 3 you created the 'acme_user' user-system, with service-prefix 'acme_user'.
> If you used a different service-prefix use that one instead.
>
> For example if you used `acme_user_di` as service_prefix then use
> `acme_user_di.user_provider.username` instead.

**Caution:**

> NEVER use `fos_user.username` or `fos_user.username_email` for your user-provider, doing so will break the authentication
> when the current user-system is changed. Using the user-system's user-provider ensures you always get the correct service.

Next, take a look at and examine the `firewalls` section. Here we have declared a firewall named `main`.
By specifying `form_login`, you have told the Symfony2 framework that any time a request is made to this
firewall that leads to the user needing to authenticate himself, the user will be redirected to a form
where he will be able to enter his credentials. It should come as no surprise
then that you have specified the user provider service we declared earlier as the
provider for the firewall to use as part of the authentication process.

**Note:**

> Although we have used the form login mechanism in this example, the FOSUserBundle
> user provider service is compatible with many other authentication methods as well.
> Please read the Symfony2 Security component documentation for more information on the
> other types of authentication methods.

The `access_control` section is where you specify the credentials necessary for
users trying to access specific parts of your application. The bundle requires
that the login form and all the routes used to create a user and reset the password
be available to unauthenticated users but use the same firewall as
the pages you want to secure with the bundle. This is why you have specified that
any request matching the `/user/login` pattern or starting with `/user/register` or
`/user/resetting` have been made available to anonymous users. You have also specified
that any request beginning with `/admin` will require a user to have the
`ROLE_ADMIN` role.

For more information on configuring the `security.yml` file please read the Symfony2
security component [documentation](http://symfony.com/doc/current/book/security.html).

**Note:**

> Pay close attention to the name, `main`, that we have given to the firewall which
> your user-system is configured in. This is the same name as you configured for the user-system.
>
> And make sure the firewall matching also matches for the corresponding user-system,
> else the UserDiscriminator will not be able to determine the current user-system.

### 5: Configure the RollerworksMultiUserBundle

You don't have to configure anything for the RollerworksMultiUserBundle
as most configuration is handled per user-system.

What you properly want to configure is the `from_email` so that any user system
that has none configured this explicitly, will inherit from this.

``` yaml
# app/config/config/config.yml

rollerworks_multi_user:
    from_email:
        address: webmaster@example.com
        sender_name: webmaster
```

## Next Steps

Now that you have completed the basic installation and configuration of your user-bundle,
you are ready to learn about more advanced features and usages
of the system.

**Note:**

> Keep in mind that most of the documentation will refer back to the FOSUserBundle,
> the documentation of RollerworksMultiUserBundle only describes
> what is different compared to FOSUserBundle.

- [Overriding Templates](overriding_templates.md)
- [Overriding Forms](overriding_forms.md)
- [Using the UserManager and GroupManager](user_manager.md)
