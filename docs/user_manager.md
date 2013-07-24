About RollerworksMultiUserBundle User Manager
=============================================

The RollerworksMultiUserBundle only provides one User Manager class: `DelegatingUserManager`
which delegates all calls to the actual User Manager of the current user-system.

The `fos_user.user_manager` service should only be used when you want to manage the 'current' user type,
to manage a specific user type you must use the `[service-prefix].user_manager` like `acme_user.user_manager`.

> The `[service-prefix]` is what you have configured for the user-system, in this case 'acme_user'

Handling of user groups is pretty much the same, except that you use `acme_user.group_manager` for handling groups.

**Note:**

> Groups support must be enabled per user-system, by default the system uses the `NoopGroupManager` which does nothing.
> See also: [Using Groups With FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/groups.md)

## Controller

Because the FOSUserBundle Controllers always use the current user-system,
you must either create your own or change the current user-system.

The user discriminator is available in the container as the rollerworks_multi_user.user_discriminator service.

**Caution:** Make sure to use the user-system name for setCurrentUser(), and not the service-prefix.

```php
$userDiscriminator = $container->get('rollerworks_multi_user.user_discriminator');
$userDiscriminator->setCurrentUser('user-system-name');

// Example
$userDiscriminator->setCurrentUser('acme_user');
```

**Note:**

> As the group manager is connected to the user-system, this must also be done when
> managing groups of another user-system.
