Getting Started With RollerworksMultiUserBundle
===============================================

The FOSUserBundle a powerful user management solution, but can only support one user type per installation.

RollerworksMultiUserBundle provides a framework on top of the FOSUserBundle to support
multiple users at the same time.

Because its build on-top of the FOSUserBundle, you don't have to change existing bundles to use this bundle.

## Prerequisites

You need at least Symfony 2.3, FOSUserBundle 2.0 (2.0.0-alpha1) and the Composer package manager.

## Installation

1. Download RollerworksMultiUserBundle
2. Enable the Bundle
3. Create your UserSystem(s)
4. Configure your application's security.yml
5. Configure the RollerworksMultiUserBundle

**Note:**

> The RollerworksMultiUserBundle allows you to create as many user-systems as you need,
> just repeat all the steps described in section 3 for each system you want to create.
>
> But. Remember that each user-system must have its own: name, firewall and request-matcher
> to not cause any conflict.

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

### 2: Enable the bundle

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

**Caution:** Make sure FOSUserBundle always comes before the RollerworksMultiUserBundle.

**Note:** If you want to use the SonataUserBundle make sure to NOT set the FOSUserBundle as parent,
every bundle can only have one parent.

### 3: Create your User(s)

To create your new user(s), you can either

use the `rollerworks:multi-user:generate:usersys` command to get the skeleton generated for you ()

&nbsp; or

create everything by hand ([see creating classes by hand](creating_classes_by_hand.md)).


**Note:**

> Each user-system is required to have its own Form types to functional properly,
you can not reuse the form types of UserA for UserB. If you don't specify anyone explicit
the system will register the user-system form-types for you.

#### 3.1: Create your user(s) with the command line

To create your new user-system, first create a new bundle skeleton.


``` bash
$ php app/console rollerworks:multi-user:generate:usersys
```

**Note:**

> Only the classes are generated, you need create the Mapping data for db-driver yourself.

**example:**

Basic annotation mapping :

``` php
<?php
namespace Acme\UserBundle;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
/**
* @ORM\Entity
* @ORM\Table(name="acme_user")
*/
class User extends BaseUser
{
/**
* @ORM\Id
* @ORM\Column(type="integer")
* @ORM\GeneratedValue(strategy="AUTO")
*/
protected $id;
}
```


> In the example all routing path's will be prefixed with 'user/' for explicitness,
> you can decide not to use them or use your own.

**Note:**

> Using this method requires the SensioGeneratorBundle to be installed and enabled,
> which is the case for the symfony-standard edition.


For this example we'll be using `AcmeUserBundle` as our bundle name,
which will be planed in the `Acme\UserBundle` namespace with YAML as configuration format.

#### 3.2: Configure routing

Now that you have created you user class you have to configure the routing.

**Note:**

> Make sure the route-name prefix is the same as configured in the user-system.
> In practice this means replacing the `fos_user` prefix with `acme_user`.

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

<a name="security">
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

## How it works

The system works with a `UserDiscriminator`-service which determines which user-system should be handled,
and delegates all handling to actual user-services.

**Note:**

>The original fos_user service definitions and configuration are overwritten and automatically
configured for multi user support.

**Caution:**

> Configuration for the FOSUserBundle is handled trough the RollerworksMultiUserBundle,
you must not set the `fos_user` (or remove it if you have it configured) configuration in your app/config,
use `rollerworks_multi_user` instead. Setting the fos_user configuration yourself **will**
break the RollerworksMultiUserBundle.

**Note:**
> Finding the correct user is done using the AuthenticationListener and RequestListener services.
> You can also choose to build your own discriminator service, just be careful.

A user-system is also referred to as a 'user-bundle'.

## Commands

The original commands can be used as normal, but require you also include the '--user-system' parameter,
to indicate which user-system must be used.

*The user-system name is the first parameter you pass to UserServicesFactory::create()*

```bash
php app/console fos:user:create --user-system=acme_user matthieu
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
- [Creating classes by hand](creating_classes_by_hand.md)
