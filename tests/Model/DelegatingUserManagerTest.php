<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Model;

use Rollerworks\Bundle\MultiUserBundle\Model\DelegatingUserManager;
use Rollerworks\Bundle\MultiUserBundle\Model\UserConfig;
use Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User;

class DelegatingUserManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DelegatingUserManager
     */
    protected $delegatingUserManager;

    protected $user;

    public function testCreateUser()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('createUser');
        $this->delegatingUserManager->createUser();
    }

    public function testDeleteUser()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('deleteUser')->with($this->user);
        $this->delegatingUserManager->deleteUser($this->user);
    }

    public function testFindUserBy()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('findUserBy')->with(array('something'))->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->delegatingUserManager->findUserBy(array('something')));
    }

    public function testFindUserByUsername()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('findUserByUsername')->with('the-doctor')->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->delegatingUserManager->findUserByUsername('the-doctor'));
    }

    public function testFindUserByEmail()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('findUserByEmail')->with('info@example.com')->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->delegatingUserManager->findUserByEmail('info@example.com'));
    }

    public function testFindUserByUsernameOrEmail()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('findUserByUsernameOrEmail')->with('info@example.com')->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->delegatingUserManager->findUserByUsernameOrEmail('info@example.com'));
    }

    public function testFindUserByConfirmationToken()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('findUserByConfirmationToken')->with('fsw35212424278262631')->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->delegatingUserManager->findUserByConfirmationToken('fsw35212424278262631'));
    }

    public function testFindUsers()
    {
        $users = array(new User(), new User());

        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('findUsers')->will($this->returnValue($users));
        $this->assertEquals($users, $this->delegatingUserManager->findUsers());
    }

    public function testGetClass()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('getClass');
        $this->delegatingUserManager->getClass();
    }

    public function testReloadUser()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('reloadUser')->with($this->user);
        $this->delegatingUserManager->reloadUser($this->user);
    }

    public function testUpdateUser()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('updateUser')->with($this->user);
        $this->delegatingUserManager->updateUser($this->user);
    }

    public function testUpdateCanonicalFields()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('updateCanonicalFields')->with($this->user);
        $this->delegatingUserManager->updateCanonicalFields($this->user);
    }

    public function testUpdatePassword()
    {
        $this->delegatingUserManager->getUserDiscriminator()->getCurrentUserConfig()->getUserManager()->expects($this->once())->method('updatePassword')->with($this->user);
        $this->delegatingUserManager->updatePassword($this->user);
    }

    protected function setUp()
    {
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userConfig = new UserConfig('stub', 'stub', $userManager, $groupManager);

        $userDiscriminator = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface');
        $userDiscriminator->expects($this->exactly(2))->method('getCurrentUserConfig')->will($this->returnValue($userConfig));

        $this->user = $this->getMock('FOS\UserBundle\Model\UserInterface');

        $this->delegatingUserManager = new DelegatingUserManager($userDiscriminator);
    }
}
