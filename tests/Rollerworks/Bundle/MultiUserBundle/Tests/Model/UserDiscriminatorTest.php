<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Model;

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
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $config = new UserConfig('acme_user', 'acme_user_route', $userManager, $groupManager);

        $userManager2 = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager2 = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager2 */
        $config2 = new UserConfig('acme_user', 'acme_user_route', $userManager2, $groupManager2);

        $this->discriminator = new UserDiscriminator(array('user1' => $config, 'user2' => $config2));
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
}
