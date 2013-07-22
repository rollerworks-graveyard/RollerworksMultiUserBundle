Getting Started With RollerworksMultiUserBundle
===============================================

The FOSUserBundle a powerful user management solution, but can only support one user type per installation.
RollerworksMultiUserBundle provides a framework on top of the FOSUserBundle to support
multiple users at the same time.

Because its build on-top of the FOSUserBundle, you don't have to change existing bundles to use this bundle.

## Prerequisites

You need at least Symfony 2.2, FOSUserBundle 2.0 and the Composer package manager.

> Using at least Symfony 2.3 and installing the symfony/proxy-manager-bridge is
> highly recommended so UserSystems can be lazy loaded then.
>
> http://symfony.com/doc/current/components/dependency_injection/lazy_services.html

Most of the configuration and creating of classes is kept in sync with the FOSUserBundle,
so if something is not described in detail here you can read the FOSUserBundle documentation as reference.

**Note:** Using the app/console commands of the FOSUserBundle is currently not supported.

## Working

The system works with a `UserDiscriminator`-service which determines which user-type should be handled,
and delegates all handling to actual user-services.

**Note:** The original fos_user service definitions and configuration are overwritten and automatically set
witch delegated versions, setting the fos_user configuration will break this bundle.

> Finding the correct user is done using the RequestMatcher and session key (for speed),
> but you can also choose to build your own discriminator service.

## Installation

**Note:**

> As this bundle allows to create, multiple user-systems, you can repeat the steps from 3. to create as many as you need.
> And remember that each user-system must have its own name, firewall, request-matcher to cause any conflicts.

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
    );
}
```

### 3: Create your UserSystem(s)

To create your user-system, first create a new bundle skeleton.

> There are some plans on providing a app/console command to easily create a new user-bundle skeleton,
> with all the routing and such already configured.

``` bash
$ php app/console generate:bundle
```

See: [Generating a New Bundle Skeleton](http://symfony.com/doc/current/bundles/SensioGeneratorBundle/commands/generate_bundle.html)
    for more details on creating a new bundle skeleton.

For this example we will be using `AcmeUserBundle` as bundle name, in the `Acme\UserBundle` namespace with YAML as configuration format.

All the routing path's will be prefixed with 'user/' for explicitness, you can decide not to use them or use your own.

#### 3.1: Create your User class

See [FOSUserBundle - Create your User class](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md#step-3-create-your-user-class) for full details.

#### 3.2: Register the user-system

Registering the system happens only with enabling your user bundle.

To make registering all the service a lot easier you can use the `UserServicesFactory`,
which will then register all services for you.

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

                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'db_driver' => 'orm',
                'model_manager_name' => 'default',

                'from_email' => array(
                    'address' => 'info@example.com',
                    'sender_name' => 'webmaster',
                ),

                'user_class' => 'Acme\UserBundle\Entity\User',
                'firewall_name' => 'user',

                'group' => array(
                    'group_class' => 'Acme\UserBundle\Entity\Group'
                ),

                'profile' => array(
                    'template' => array(
                        'edit' => 'AcmeUserBundle:Profile:edit.html.twig',
                        'show' => 'AcmeUserBundle:Profile:show.html.twig',
                    ),
                    'form' => array(
                        'type' => 'acme_user_profile',
                        'class' => 'Acme\UserBundle\Form\Type\ProfileFormType',
                        'name' => 'acme_user_profile_form',
                    ),
                ),

                'registration' => array(
                    'template' => array(
                        'register' => 'AcmeUserBundle:Registration:register.html.twig',
                        'check_email' => 'AcmeUserBundle:Registration:checkEmail.html.twig',
                    ),
                    'form' => array(
                        'type' => 'acme_user_registration',
                        'class' => 'Acme\UserBundle\Form\Type\RegistrationFormType',
                        'name' => 'acme_user_registration_form',
                    ),
                ),

                'resetting' => array(
                    'template' => array(
                        'check_email' => 'AcmeUserBundle:Resetting:checkEmail.html.twig',
                        'email' => 'AcmeUserBundle:Resetting:email.txt.twig',
                        'password_already_requested' => 'AcmeUserBundle:Resetting:passwordAlreadyRequested.html.twig',
                        'request' => 'AcmeUserBundle:Resetting:request.html.twig',
                        'reset' => 'AcmeUserBundle:Resetting:reset.html.twig',
                    )
                ),

                'change_password' => array(
                    'template' => array(
                        'change_password' => 'AcmeUserBundle:changePassword:changePassword.html.twig',
                    )
                ),
            )
        ));
    }
}
```

`UserServicesFactory::create()` will register a new user-system in the `ContainerBuilder` for you.

The first parameter is the name of the user-system, the second is the configuration which internally
is normalized by the Symfony Config component. In the next section will learn how you can use this
to make your bundle more configurable.

> You can also choose to add more then one user-system in your bundle,
> but from a separation perspective its better to only use one bundle per user-system.

The configuration above is actually a pretty verbose example, everything except 'user_class' and is 'firewall_name' is optional.
When 'services_prefix' and 'routes_prefix' are empty they inherit the user-system's name, which is given with the first parameter of `UserServicesFactory::create()`.

**Note:**

> `path` and `host` are used by the UserDiscriminator service for finding the correct user-type.
> You can either set only a path or host, or both.
> Or you can choose to use your own matcher-service by using the `request_matcher`, and giving it the service-id.
>
> A request-matcher must always implement the `Symfony\Component\HttpFoundation\RequestMatcherInterface`.

**Caution:** Never set the template namespace to FOSUserBundle or RollerworksMultiUserBundle as that creates a loop-back!

For more details on configuration reference see [FOSUserBundle Configuration Reference](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/configuration_reference.md)

#### 3.3: Make your bundle configurable

As you don't want to hard-code the configuration in your bundle, you can add the following to make your bundle more configurable.

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
        $config = $this->processConfiguration($configuration, $configs);

        $factory = new UserServicesFactory($container);
        $factory->create('acme_user', array(
            array(
                'path' => '^/user',
                'host' => null,

                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'db_driver' => 'orm',
                'user_class' => 'Acme\UserBundle\Entity\User',
                'firewall_name' => 'user',

                // ...
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

Now you can easily configuring the user-bundle in your `app/config/config.yml` config using the `acme_user` configuration-tree.

> You can limit what is configurable by passing an array as second parameter to `UserConfiguration::addUserConfig()`
> Available values are: 'profile', 'change_password', 'registration', 'resetting', 'group'.

**Tip:** The `UserServicesFactory::create()` will automatically strip any unknown configuration-keys that are passed to it.

##### Placing the configuration under its own level.

If you don't want to add the user-configuration at root level, use the following.

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

And if bundle configuration is made possible (see previous sub-section).

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

First copy the `Resources/config/routing` from the FOSUserBundle to your user-bundle.

Now replace all the `fos_user` route prefixes to `acme_user` or what you have configured for your bundle.

By importing the routing files you will have ready made pages for things such as
log-in, creating users, etc.

In YAML:

``` yaml
# app/config/routing.yml
acme_user_security:
    resource: "@AcmeUserBundle/Resources/config/routing/security.xml"
    prefix: /user/profile

acme_user_profile:
    resource: "@AcmeUserBundle/Resources/config/routing/profile.xml"
    prefix: /user/profile

acme_user_register:
    resource: "@AcmeUserBundle/Resources/config/routing/registration.xml"
    prefix: /user/register

acme_user_resetting:
    resource: "@AcmeUserBundle/Resources/config/routing/resetting.xml"
    prefix: /user/resetting

acme_user_change_password:
    resource: "@AcmeUserBundle/Resources/config/routing/change_password.xml"
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

### 4: Configure your application's security.yml

In order for Symfony's security component to use the user-system, you must
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
            pattern: ^/
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

Next, take a look at and examine the `firewalls` section. Here we have declared a
firewall named `main`. By specifying `form_login`, you have told the Symfony2
framework that any time a request is made to this firewall that leads to the
user needing to authenticate himself, the user will be redirected to a form
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
> else the UserDiscriminator-service will not be able to determine the current user-system.

### 5: Configure the RollerworksMultiUserBundle

**Note:** Remember that the original fos_user service definitions and configuration
are overwritten and automatically set witch delegated versions,
setting the fos_user configuration will break this bundle.

You don't really have set anything for the RollerworksMultiUserBundle
as most configuration is handled per user-system.

What you properly want to configure is the `from_email` so that any user system
that has no configured this explicitly will inherit this.

``` yaml
# app/config/config/config.yml

rollerworks_multi_user:

    # Enable the listeners for the storage engine
    use_listener: true

    from_email:
        address: webmaster@example.com
        sender_name: webmaster
```

## Running the Tests

Before running the tests, you will need to install the bundle dependencies. Do this using composer:

``` bash
$ php composer.phar --dev install
```

Then you can launch phpunit (make sure its installed https://github.com/sebastianbergmann/phpunit/#installation)

> Using the Composer version of PHPUnit currently fails so make sure to either use the Phar archive or PEAR version.
> You need at least version 3.5 of PHPUnit and MockObject plugin 1.0.8

``` bash
$ phpunit -c phpunit.xml.dist
```

**Note:** Functional test are by default not run, to run all tests make sure PDO_SQLite is installed,
and launch phpunit with:

``` bash
$ bin/phpunit -c phpunit.xml.dist --exclude-group ""
```
