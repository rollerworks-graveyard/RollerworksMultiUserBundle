<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\DependencyInjection\Compiler;

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler\RegisterUserPass;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterUserPassTest extends \PHPUnit_Framework_TestCase
{
    public function testDisabled()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterUserPass();
        $pass->process($container);
    }

    public function testRegister()
    {
        $container = new ContainerBuilder();

        $config = array(
            array(
                'request_matcher' => 'acme_user.request_matcher',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            ),
        );

        $factory = new UserServicesFactory($container);
        $factory->create('acme', $config);

        $authenticationListener = $container->register('rollerworks_multi_user.listener.authentication', 'StdClass');
        $requestListener = $container->register('rollerworks_multi_user.listener.request', 'StdClass');
        $userDiscriminator = $container->register('rollerworks_multi_user.user_discriminator', 'StdClass');

        $pass = new RegisterUserPass();
        $pass->process($container);

        $this->assertEquals(array(array('addUser', array('acme', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User'))), $authenticationListener->getMethodCalls());
        $this->assertEquals(array(array('addUser', array('acme', new Reference('acme_user.request_matcher')))), $requestListener->getMethodCalls());
        $this->assertEquals(array(array('addUser', array('acme', new Reference('rollerworks_multi_user.user_system.acme')))), $userDiscriminator->getMethodCalls());
    }

    public function testRegisterTwoUsers()
    {
        $container = new ContainerBuilder();

        $config = array(
            array(
                'request_matcher' => 'acme_user.request_matcher',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            ),
        );

        $factory = new UserServicesFactory($container);
        $factory->create('acme', $config);

        $config = array(
            array(
                'request_matcher' => 'sf_user.request_matcher',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\AnotherUser',
                'services_prefix' => 'sf_user',
                'routes_prefix' => 'sf_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            ),
        );

        $factory = new UserServicesFactory($container);
        $factory->create('sf', $config);

        $authenticationListener = $container->register('rollerworks_multi_user.listener.authentication', 'StdClass');
        $requestListener = $container->register('rollerworks_multi_user.listener.request', 'StdClass');
        $userDiscriminator = $container->register('rollerworks_multi_user.user_discriminator', 'StdClass');

        $pass = new RegisterUserPass();
        $pass->process($container);

        $this->assertEquals(array(
            array('addUser', array('acme', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User')),
            array('addUser', array('sf', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\AnotherUser')),
        ), $authenticationListener->getMethodCalls());

        $this->assertEquals(array(
            array('addUser', array('acme', new Reference('acme_user.request_matcher'))),
            array('addUser', array('sf', new Reference('sf_user.request_matcher'))),
        ), $requestListener->getMethodCalls());

        $this->assertEquals(array(
            array('addUser', array('acme', new Reference('rollerworks_multi_user.user_system.acme'))),
            array('addUser', array('sf', new Reference('rollerworks_multi_user.user_system.sf'))),
        ), $userDiscriminator->getMethodCalls());
    }

    public function testRegisterWithAutoRequestMatcher()
    {
        $container = new ContainerBuilder();

        $config = array(
            array(
                'path' => '^admin/',
                'host' => 'example.com',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            ),
        );

        $factory = new UserServicesFactory($container);
        $factory->create('acme', $config);

        $authenticationListener = $container->register('rollerworks_multi_user.listener.authentication', 'StdClass');
        $requestListener = $container->register('rollerworks_multi_user.listener.request', 'StdClass');
        $userDiscriminator = $container->register('rollerworks_multi_user.user_discriminator', 'StdClass');

        $pass = new RegisterUserPass();
        $pass->process($container);

        $this->assertEquals(array(
            array('addUser', array('acme', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User')),
        ), $authenticationListener->getMethodCalls());

        $this->assertEquals(array(
            array('addUser', array('acme', new Reference('rollerworks_multi_user.user_system.acme'))),
        ), $userDiscriminator->getMethodCalls());

        $this->assertTrue($requestListener->hasMethodCall('addUser'));

        $calls = $requestListener->getMethodCalls();
        $requestMatcher = $calls[0][1][1];
        /** @var Reference $requestMatcher */

        $def = $container->getDefinition((string) $requestMatcher);
        $this->assertEquals('%rollerworks_multi_user.matcher.class%', $def->getClass());
        $this->assertEquals(array('^admin/', 'example.com'), $def->getArguments());
    }

    public function testRegisterWithAutoRequestMatcherException()
    {
        $container = new ContainerBuilder();
        $factory = new UserServicesFactory($container);

        $config = array(
            array(
                'path' => '^admin/',
                'host' => 'example.com',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            ),
        );

        $factory->create('acme', $config);

        $config = array(
            array(
                'path' => '^admin/',
                'host' => 'example.com',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'sf_user',
                'routes_prefix' => 'sf_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            ),
        );

        $factory->create('sf', $config);

        $container->register('rollerworks_multi_user.listener.authentication', 'StdClass');
        $container->register('rollerworks_multi_user.listener.request', 'StdClass');
        $container->register('rollerworks_multi_user.user_discriminator', 'StdClass');

        $pass = new RegisterUserPass();

        $this->setExpectedException('RuntimeException', 'There is already a request-matcher for this configuration: path: "^admin/", host: "example.com". Only one request matcher should match for the user system.');
        $pass->process($container);
    }
}
