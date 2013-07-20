<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @todo Test canonicalizer
 * @todo Test custom user-manager setting
 */
class UserServicesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    public function testRegisterBasic()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => '/', 'host' => null)), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $this->assertEquals(array('acme_user', 'acme_user', new Reference('acme_user.user_manager'), new Reference('acme_user.group_manager')), $def->getArguments());
    }

    public function testRequestMatcher()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

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
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => null, 'host' => null, 'request_matcher' => 'acme_user.request_matcher')), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $this->assertEquals(array('acme_user', 'acme_user', new Reference('acme_user.user_manager'), new Reference('acme_user.group_manager')), $def->getArguments());
    }

    /**
     * @dataProvider provideModelManagerConfigs
     */
    public function testModelManager($driver, $service, $class)
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'db_driver' => $driver,
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.acme_user.model_manager'));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.acme_user.model_manager');
        $this->assertEquals($service, $def->getFactoryService());
        $this->assertEquals($class, $def->getClass());

        $this->assertEquals('getManager', $def->getFactoryMethod());
        $this->assertEquals(array('default'), $def->getArguments());
    }

    public function testDefaultMailer()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.mailer'));
        $this->assertTrue($this->containerBuilder->hasDefinition('acme_user.mailer.default'));
        $this->assertEquals('acme_user.mailer.default', (string) $this->containerBuilder->getAlias('acme_user.mailer'));

        $def = $this->containerBuilder->getDefinition('acme_user.mailer.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.routing.user_discriminator_url_generator'), $def->getArgument(1));
        $this->assertEquals(array(
            'confirmation.template' => sprintf('%%%s.registration.confirmation.email.template%%', 'acme_user'),
            'resetting.template' => sprintf('%%%s.resetting.email.template%%', 'acme_user'),
            'from_email' => array(
                'confirmation' => sprintf('%%%s.registration.confirmation.from_email%%', 'acme_user'),
                'resetting' => sprintf('%%%s.resetting.email.from_email%%', 'acme_user'),
            )
        ), $def->getArgument(3));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig', 'acme_user', 'registration.confirmation', 'email', $def);
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig', 'acme_user', 'resetting', 'email', $def);
    }

    public function testTwigSwiftMailer()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'service' => array(
                    'mailer' => 'fos_user.mailer.twig_swift',
                ),

                'profile' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.mailer'));
        $this->assertTrue($this->containerBuilder->hasDefinition('acme_user.mailer.twig_swift'));
        $this->assertEquals('acme_user.mailer.twig_swift', (string) $this->containerBuilder->getAlias('acme_user.mailer'));

        $def = $this->containerBuilder->getDefinition('acme_user.mailer.twig_swift');
        $this->assertEquals(new Reference('rollerworks_multi_user.routing.user_discriminator_url_generator'), $def->getArgument(1));
        $this->assertEquals(array(
            'template' => array(
                'confirmation' => sprintf('%%%s.registration.confirmation.email.template%%', 'acme_user'),
                'resetting' => sprintf('%%%s.resetting.email.template%%', 'acme_user')
            ),
            'from_email' => array(
                'confirmation' => sprintf('%%%s.registration.confirmation.from_email%%', 'acme_user'),
                'resetting' => sprintf('%%%s.resetting.email.from_email%%', 'acme_user'),
            )
        ), $def->getArgument(3));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig', 'acme_user', 'registration.confirmation', 'email', $def);
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig', 'acme_user', 'resetting', 'email', $def);
    }

    public function testCustomMailer()
    {
        $this->containerBuilder->register('acme_mailer.user_mailer', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub');

        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'service' => array(
                    'mailer' => 'acme_mailer.user_mailer',
                ),

                'profile' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.mailer'));
        $this->assertTrue($this->containerBuilder->hasDefinition('acme_mailer.user_mailer'));
        $this->assertEquals('acme_mailer.user_mailer', (string) $this->containerBuilder->getAlias('acme_user.mailer'));

        $def = $this->containerBuilder->getDefinition('acme_mailer.user_mailer');
        $this->assertEquals(array(), $def->getArguments());

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig', 'acme_user', 'registration.confirmation', 'email', $def);
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig', 'acme_user', 'resetting', 'email', $def);
    }

    public function testCustomMailerNoOverwrite()
    {
        $this->containerBuilder->register('acme_user.mailer', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub');

        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'service' => array(
                    'mailer' => 'acme_user.mailer',
                ),

                'profile' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertFalse($this->containerBuilder->hasAlias('acme_user.mailer'));
        $this->assertTrue($this->containerBuilder->hasDefinition('acme_user.mailer'));

        $def = $this->containerBuilder->getDefinition('acme_user.mailer');
        $this->assertEquals(array(), $def->getArguments());

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig', 'acme_user', 'registration.confirmation', 'email', $def);
        $this->assertTemplateConfigEqual('RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig', 'acme_user', 'resetting', 'email', $def);
    }

    public function testProfileConfiguration()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'registration' => false,
                'resetting' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => '/', 'host' => null)), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $expected = array(
            'class' => 'FOS\UserBundle\Form\Type\ProfileFormType',
            'type' => 'fos_user_profile',
            'name' => 'fos_user_profile_form',
            'validation_groups' => array('Profile', 'Default'),
        );

        $this->assertFormDefinitionEqual($expected, 'acme_user', 'profile', $def);

        $expected = array(
            'edit' => 'RollerworksMultiUserBundle:UserBundle/Profile:edit.html.twig',
            'show' => 'RollerworksMultiUserBundle:UserBundle/Profile:show.html.twig',
        );

        foreach ($expected as $name => $resource) {
            $this->assertTemplateConfigEqual($resource, 'acme_user', 'profile', $name, $def);
        }
    }

    public function testRegistrationConfiguration()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'resetting' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => '/', 'host' => null)), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $expected = array(
            'class' => 'FOS\UserBundle\Form\Type\RegistrationFormType',
            'type' => 'fos_user_registration',
            'name' => 'fos_user_registration_form',
            'validation_groups' => array('Registration', 'Default'),
        );

        $this->assertFormDefinitionEqual($expected, 'acme_user', 'registration', $def);

        $expected = array(
            'register' => 'RollerworksMultiUserBundle:UserBundle/Registration:register.html.twig',
            'check_email' => 'RollerworksMultiUserBundle:UserBundle/Registration:checkEmail.html.twig',
        );

        foreach ($expected as $name => $resource) {
            $this->assertTemplateConfigEqual($resource, 'acme_user', 'registration', $name, $def);
        }
    }

    public function testResettingConfiguration()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'change_password' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => '/', 'host' => null)), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $found = false;
        foreach ($def->getMethodCalls() as $call) {
            if ('setConfig' !== $call[0]) {
                continue;
            }

            if ('resetting.token_ttl' === $call[1][0]) {
                $found = true;
                $this->assertEquals('%acme_user.resetting.token_ttl%', $call[1][1]);

                break;
            }
        }

        if (!$found) {
            $this->fail('Failed finding the tokenTtl configuration.');
        }

        $expected = array(
            'class' => 'FOS\UserBundle\Form\Type\ResettingFormType',
            'type' => 'fos_user_resetting',
            'name' => 'fos_user_resetting_form',
            'validation_groups' => array('Resetting', 'Default'),
        );

        $this->assertFormDefinitionEqual($expected, 'acme_user', 'resetting', $def);

        $expected = array(
            'check_email' => 'RollerworksMultiUserBundle:UserBundle/Resetting:checkEmail.html.twig',
            'email' => 'RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig',
            'password_already_requested' => 'RollerworksMultiUserBundle:UserBundle/Resetting:passwordAlreadyRequested.html.twig',
            'request' => 'RollerworksMultiUserBundle:UserBundle/Resetting:request.html.twig',
            'reset' => 'RollerworksMultiUserBundle:UserBundle/Resetting:reset.html.twig',
        );

        foreach ($expected as $name => $resource) {
            $this->assertTemplateConfigEqual($resource, 'acme_user', 'resetting', $name, $def);
        }
    }

    public function testChangePasswordConfiguration()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => '/', 'host' => null)), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $expected = array(
            'class' => 'FOS\UserBundle\Form\Type\ChangePasswordFormType',
            'type' => 'fos_user_change_password',
            'name' => 'fos_user_change_password_form',
            'validation_groups' => array('ChangePassword', 'Default'),
        );

        $this->assertFormDefinitionEqual($expected, 'acme_user', 'change_password', $def);

        $expected = array(
            'change_password' => 'RollerworksMultiUserBundle:UserBundle/ChangePassword:changePassword.html.twig',
        );

        foreach ($expected as $name => $resource) {
            $this->assertTemplateConfigEqual($resource, 'acme_user', 'change_password', $name, $def);
        }
    }

    public function testGroupConfiguration()
    {
        $factory = new UserServicesFactory($this->containerBuilder);

        $config = array(
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',

                'profile' => false,
                'registration' => false,
                'resetting' => false,
                'change_password' => false,

                'group' => array(
                    'group_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\Group',
                )
            )
        );

        $factory->create('acme', $config);

        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.user_manager'));
        $this->assertTrue($this->containerBuilder->hasAlias('acme_user.group_manager'));

        $this->assertTrue($this->containerBuilder->hasDefinition('rollerworks_multi_user.user_system.acme'));

        $def = $this->containerBuilder->getDefinition('acme_user.user_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(3));
        $this->assertEquals('%acme_user.model.user.class%', $def->getArgument(4));

        $def = $this->containerBuilder->getDefinition('acme_user.group_manager.default');
        $this->assertEquals(new Reference('rollerworks_multi_user.acme_user.model_manager'), $def->getArgument(0));
        $this->assertEquals('%acme_user.model.group.class%', $def->getArgument(1));

        $def = $this->containerBuilder->getDefinition('rollerworks_multi_user.user_system.acme');
        $this->assertEquals('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig', $def->getClass());
        $this->assertEquals(array(array('alias' => 'acme', 'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User', 'path' => '/', 'host' => null)), $def->getTag('rollerworks_multi_user.user_system'));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $this->assertTrue($def->isLazy());
        }

        $expected = array(
            'class' => 'FOS\UserBundle\Form\Type\GroupFormType',
            'type' => 'fos_user_change_password',
            'name' => 'fos_user_change_password_form',
            'validation_groups' => array('Registration', 'Default'),
        );

        $this->assertFormDefinitionEqual($expected, 'acme_user', 'group', $def);

        $expected = array(
            'edit' => 'RollerworksMultiUserBundle:UserBundle/Group:edit.html.twig',
            'list' => 'RollerworksMultiUserBundle:UserBundle/Group:list.html.twig',
            'new' => 'RollerworksMultiUserBundle:UserBundle/Group:new.html.twig',
            'show' => 'RollerworksMultiUserBundle:UserBundle/Group:show.html.twig',
        );

        foreach ($expected as $name => $resource) {
            $this->assertTemplateConfigEqual($resource, 'acme_user', 'group', $name, $def);
        }
    }

    public static function provideModelManagerConfigs()
    {
        return array(
            array('orm', 'doctrine', 'Doctrine\ORM\EntityManager'),
            array('mongodb', 'doctrine_mongodb', 'Doctrine\ODM\MongoDB\DocumentManager'),
            array('couchdb', 'doctrine_couchdb', 'Doctrine\ODM\CouchDB\DocumentManager'),
        );
    }

    protected function assertFormDefinitionEqual($expected, $servicePrefix, $type, Definition $userSystem = null)
    {
        $formDefinition = $this->containerBuilder->getDefinition(sprintf('%s.%s.form.type', $servicePrefix, $type));

        if (isset($expected['class'])) {
            $this->assertEquals($expected['class'], $formDefinition->getClass());
        }

        if (isset($expected['alias'])) {
            $this->assertEquals(array(array('alias' => $expected['alias'])), $formDefinition->getTag('form.type'));
        }

        if ('resetting' !== $type) {
            $formFactoryDefinition = $this->containerBuilder->getDefinition(sprintf('%s.%s.form.factory', $servicePrefix, $type));

            $this->assertEquals(sprintf('%%%s.%s.form.name%%', $servicePrefix, $type), $formFactoryDefinition->getArgument(1));
            $this->assertEquals(sprintf('%%%s.%s.form.type%%', $servicePrefix, $type), $formFactoryDefinition->getArgument(2));
            $this->assertEquals(sprintf('%%%s.%s.form.validation_groups%%', $servicePrefix, $type), $formFactoryDefinition->getArgument(3));
        }

        if ($userSystem) {
            foreach ($userSystem->getMethodCalls() as $call) {
                if ('setForm' !== $call[0]) {
                    continue;
                }

                $this->assertEquals(array(
                    $type,
                    sprintf('%%%s.%s.form.name%%', $servicePrefix, $type),
                    sprintf('%%%s.%s.form.type%%', $servicePrefix, $type),
                    sprintf('%%%s.%s.form.validation_groups%%', $servicePrefix, $type)
                ), $call[1]);
            }
        }
    }

    protected function assertTemplateConfigEqual($expected, $servicePrefix, $section, $name, Definition $userSystem = null)
    {
        $actual = $this->containerBuilder->getParameter(sprintf($servicePrefix . '.%s.%s.template', $section, $name));
        $this->assertEquals($expected, $actual);

        if ($userSystem) {
            $found = false;

            foreach ($userSystem->getMethodCalls() as $call) {
                if ('setTemplate' !== $call[0]) {
                    continue;
                }

                if ($call[1][0] === sprintf('%s.%s', $section, $name)) {
                    $found = true;
                    $this->assertEquals(sprintf('%%%s.%s.%s.template%%', $servicePrefix, $section, $name), $call[1][1]);

                    break;
                }
            }

            if (!$found) {
                $this->fail(sprintf('No template configuration found for: "%s.%s".', $section, $name));
            }
        }
    }

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
    }
}
