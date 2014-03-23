Overriding Default FOSUserBundle Forms
======================================

See [Overriding Default FOSUserBundle Forms](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_forms.md)
 for full details.

**Note:**

> Each user-system must have its own form-types, you can not reuse the form types of UserA for UserB
> as a form form-type can only be registered once, make sure `getName()` returns a unique name.

Replace the `%fos_user.model.user.class%` with `%[service-prefix].model.user.class%` to get the correct model class.

> `[service-prefix]` is what you have configured for the user-system.
> And don't forget to do the same for `%fos_user.model.group.class%`

### Automatic registering

The `UserServicesFactory` allows automatic registering of the Form service definition,
  to do this set the `class` configuration at the correct section with `UserServicesFactory::create()`.

> Unless you have added additional constructor parameters to your class, this is preferred way of registering a type.

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
                // ...

                'registration' => array(
                    'form' => array(
                        'type' => 'acme_user_registration',
                        'class' => 'Acme\UserBundle\Form\Type\RegistrationFormType',
                        'name' => 'acme_user_registration_form',
                    )
                )
            )
        ));
    }
}
```

This will automatically register the `acme_user.registration.form.type` service with the correct user class.

*Caution:* the `type` configuration is always required and must be the same as the returned value
of the Form's `getName()` method.
