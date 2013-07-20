<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Unit\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Rollerworks\Bundle\MultiUserBundle\Routing\UserDiscriminatorUrlGenerator;

class UserDiscriminatorUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userDiscriminator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parentUrlGenerator;

    public function testPrefixed()
    {
        $this->parentUrlGenerator
                ->expects($this->once())
                ->method('generate')
                ->with('stub_user_profile', array(), UrlGeneratorInterface::ABSOLUTE_PATH)
                ->will($this->returnValue('/profile'))
        ;

        $routeGenerator = new UserDiscriminatorUrlGenerator($this->userDiscriminator, $this->parentUrlGenerator, 'fos_user');
        $this->assertEquals('/profile', $routeGenerator->generate('fos_user_profile'));
    }

    public function testNonePrefixed()
    {
        $this->parentUrlGenerator
                ->expects($this->once())
                ->method('generate')
                ->with('my_user_profile', array(), UrlGeneratorInterface::ABSOLUTE_PATH)
                ->will($this->returnValue('/my/profile'))
        ;

        $routeGenerator = new UserDiscriminatorUrlGenerator($this->userDiscriminator, $this->parentUrlGenerator, 'fos_user');
        $this->assertEquals('/my/profile', $routeGenerator->generate('my_user_profile'));
    }

    protected function setUp()
    {
        $this->userDiscriminator = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminator')
            ->disableOriginalConstructor()->getMock();

        $user = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig')->disableOriginalConstructor()->getMock();
        $user->expects($this->any())->method('getRoutePrefix')->will($this->returnValue('stub_user'));
        $this->userDiscriminator->expects($this->any())->method('getCurrentUserConfig')->will($this->returnValue($user));

        $this->parentUrlGenerator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGeneratorInterface')
            ->disableOriginalConstructor()->getMock();
    }
}
