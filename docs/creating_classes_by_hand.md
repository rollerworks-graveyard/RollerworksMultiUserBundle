Creating classes by hand
========================

You can also create your users classes by hand.

After you downloaded and enabled the RollerworksMultiUserBundle ([see getting started](index.md)) :

#### 3.1: Create a bundle skeleton

To create your new user-system, first create a new bundle skeleton.

See: [Generating a New Bundle Skeleton](http://symfony.com/doc/current/bundles/SensioGeneratorBundle/commands/generate_bundle.html)
    for more details on creating a new bundle skeleton.

For this example we'll be using `AcmeUserBundle` as our bundle name,
which will be planed in the `Acme\UserBundle` namespace with YAML as configuration format.

#### 3.2: Create your User class

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

#### 3.3: Register the user-system

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

<a name="configuration">
#### 3.4: Make your bundle configurable

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

#### 3.5: Enable the bundle

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

And if bundle configuration is enabled ([see previous sub-section: 3.4](#configuration)).

``` yaml
# app/config/config.yml

acme_user:
    path: "^/user"
```

#### 3.6: Configure routing

Now that you have created you user class you have to configure the routing.

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
    prefix: /user

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

#### You can now go on with step 4: [Configure your application's security.yml](index.md#security)
