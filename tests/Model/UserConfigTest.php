<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Model;

use Rollerworks\Bundle\MultiUserBundle\Model\UserConfig;

class UserConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUserManager()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);
        $this->assertSame($userManager, $config->getUserManager());
        $this->assertSame($groupManager, $config->getGroupManager());

        $this->assertEquals('acme_user', $config->getServicePrefix());
        $this->assertEquals('acme_user_route', $config->getRoutePrefix());
    }

    public function testGetGroupManager()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        /** @var \FOS\UserBundle\Model\GroupManagerInterface $groupManager */
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme', 'acme', $userManager, $groupManager);
        $this->assertSame($userManager, $config->getUserManager());
        $this->assertSame($groupManager, $config->getGroupManager());
    }

    public function testForm()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $config->setForm('registration', 'fos_user_registration_form', 'fos_user_registration', array('Registration', 'Default'));

        $this->assertNull($config->getFormName('profile'));
        $this->assertNull($config->getFormType('profile'));
        $this->assertNull($config->getFormValidationGroups('profile'));

        $this->assertEquals('fos_user_registration_form', $config->getFormName('registration'));
        $this->assertEquals('fos_user_registration', $config->getFormType('registration'));
        $this->assertEquals(array('Registration', 'Default'), $config->getFormValidationGroups('registration'));
    }

    public function testConfig()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $config->setConfig('resetting.token_ttl', 86400);

        $this->assertNull($config->getConfig('foo'));
        $this->assertEquals(86400, $config->getConfig('resetting.token_ttl'));
        $this->assertEquals(86400, $config->getConfig('foo', 86400));
    }

    public function testTemplate()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $config->setTemplate('registration.confirm', 'RollerworksMultiUserBundle:UserBundle/Registration:confirmed.html.twig');
        $config->setTemplate('registration.email', 'RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig');

        $this->assertEquals('RollerworksMultiUserBundle:UserBundle/Registration:confirmed.html.twig', $config->getTemplate('registration.confirm'));
        $this->assertEquals('RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig', $config->getTemplate('registration.email'));
    }

    public function testGetEmptyTemplate()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $config->setTemplate('registration.confirm', '');

        $this->setExpectedException('Rollerworks\Bundle\MultiUserBundle\Exception\MissingTemplateException', 'Unable to get template for "registration.confirm", it seems the template value is empty. Make sure you enabled the proper section and configured a proper value.');
        $config->getTemplate('registration.confirm');
    }

    public function testGetUnregisteredTemplate()
    {
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');

        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $this->setExpectedException('Rollerworks\Bundle\MultiUserBundle\Exception\MissingTemplateException', 'Unable to get template for "profile", there is no such template configured.');
        $config->getTemplate('profile');
    }
}
