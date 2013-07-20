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

use Rollerworks\Bundle\MultiUserBundle\Model\DelegatingGroupManager;
use Rollerworks\Bundle\MultiUserBundle\Model\UserConfig;
use Rollerworks\Bundle\MultiUserBundle\Tests\Stub\Group;

class DelegatingGroupManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DelegatingGroupManager
     */
    protected $delegatingGroupManager;

    protected $group;

    public function testGetClass()
    {
        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('getClass');
        $this->delegatingGroupManager->getClass();
    }

    public function testCreateGroup()
    {
        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('createGroup')->with('foo');
        $this->delegatingGroupManager->createGroup('foo');
    }

    public function testDeleteGroup()
    {
        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('deleteGroup')->with($this->group);
        $this->delegatingGroupManager->deleteGroup($this->group);
    }

    public function testFindGroupBy()
    {
        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('findGroupBy')->with(array('foo' => 'bar'))->will($this->returnValue($this->group));
        $this->assertEquals($this->group, $this->delegatingGroupManager->findGroupBy(array('foo' => 'bar')));
    }

    public function testFindGroupByName()
    {
        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('findGroupByName')->with('foo')->will($this->returnValue($this->group));
        $this->assertEquals($this->group, $this->delegatingGroupManager->findGroupByName('foo'));
    }

    public function testFindGroups()
    {
        $groups = array(new Group(), new Group());

        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('findGroups')->will($this->returnValue($groups));
        $this->assertEquals($groups, $this->delegatingGroupManager->findGroups());
    }

    public function testUpdateGroup()
    {
        $this->delegatingGroupManager->getUserDiscriminator()->getCurrentUserConfig()->getGroupManager()->expects($this->once())->method('updateGroup')->with($this->group);
        $this->delegatingGroupManager->updateGroup($this->group);
    }

    protected function setUp()
    {
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $groupManager = $this->getMock('FOS\UserBundle\Model\GroupManagerInterface');
        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userConfig = new UserConfig('stub', 'stub', $userManager, $groupManager);

        $groupDiscriminator = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface');
        $groupDiscriminator->expects($this->exactly(2))->method('getCurrentUserConfig')->will($this->returnValue($userConfig));

        $this->group = $this->getMock('FOS\UserBundle\Model\GroupInterface');
        $this->delegatingGroupManager = new DelegatingGroupManager($groupDiscriminator);
    }
}
