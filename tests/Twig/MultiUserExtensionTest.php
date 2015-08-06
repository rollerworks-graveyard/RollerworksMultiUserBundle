<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Twig;

use Rollerworks\Bundle\MultiUserBundle\Twig\Extension\MultiUserExtension;

class MultiUserExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUser()
    {
        $user = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig')->disableOriginalConstructor()->getMock();

        $discriminator = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface');
        $discriminator->expects($this->once())->method('getCurrentUserConfig')->will($this->returnValue($user));
        $discriminator->expects($this->once())->method('getCurrentUser')->will($this->returnValue('acme'));

        $extension = new MultiUserExtension($discriminator);
        $this->assertSame($user, $extension->getCurrentUser());
        $this->assertEquals('acme', $extension->getCurrentUser(true));
    }

    public function testUsage()
    {
        $user = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig')->disableOriginalConstructor()->getMock();
        $user->expects($this->atLeastOnce())->method('getTemplate')->with('profile.edit')->will($this->returnValue('AcmeUserCoreBundle:Profile:edit.html.twig'));

        $discriminator = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface');
        $discriminator->expects($this->atLeastOnce())->method('getCurrentUserConfig')->will($this->returnValue($user));
        $discriminator->expects($this->atLeastOnce())->method('getCurrentUser')->will($this->returnValue('acme'));

        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader);
        $twig->addExtension(new MultiUserExtension($discriminator));

        $this->assertEquals('AcmeUserCoreBundle:Profile:edit.html.twig - acme', $twig->render('{{ rollerworks_multi_user_user().getTemplate("profile.edit") }} - {{ rollerworks_multi_user_user(true) }}', array('name' => 'Fabien')));
    }
}
