<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Unit\Model;

use Rollerworks\Bundle\MultiUserBundle\Model\UserConfig;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminator;

class UserDiscriminatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var UserConfig[]
     */
    protected $users;

    /**
     * @var UserDiscriminator
     */
    protected $discriminator;

    public function setUp()
    {
        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')->disableOriginalConstructor()->getMock();

        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $userManager2 = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager2 = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager2 */
        $config2 = new UserConfig('acme_user', 'acme_user_route', $userManager2, $groupManager2);

        $this->discriminator = new UserDiscriminator($this->session, array('user1' => $config, 'user2' => $config2));
        $this->users = array('user1' => $config, 'user2' => $config2);
    }

    public function testSetUserException()
    {
        $this->setExpectedException('LogicException', 'Impossible to set the user discriminator, because "user3" is not present in the users list.');
        $this->discriminator->setCurrentUser('user3');
    }

    public function testGetUser()
    {
        $this->discriminator->setCurrentUser('user2');
        $this->assertEquals('user2', $this->discriminator->getCurrentUser());
        $this->assertSame($this->users['user2'], $this->discriminator->getCurrentUserConfig());
    }

    public function testSetClassPersist()
    {
        $this->session->expects($this->exactly(1))->method('set')->with(UserDiscriminator::SESSION_NAME, 'user2');
        $this->discriminator->setCurrentUser('user2', true);
    }

    public function testGetUserDefault()
    {
        $this->session->expects($this->exactly(1))->method('get')->with(UserDiscriminator::SESSION_NAME, null)->will($this->onConsecutiveCalls(null));
        $this->assertNull($this->discriminator->getCurrentUser());
    }

    public function testGetUserConfigDefault()
    {
        $this->session->expects($this->exactly(1))->method('get')->with(UserDiscriminator::SESSION_NAME, null)->will($this->onConsecutiveCalls(null));
        $this->assertNull($this->discriminator->getCurrentUserConfig());
    }

    public function testGetClassStored()
    {
        $this->session->expects($this->exactly(1))->method('get')->with(UserDiscriminator::SESSION_NAME, null)->will($this->onConsecutiveCalls('user2'));
        $this->assertEquals('user2', $this->discriminator->getCurrentUser());
    }
}
