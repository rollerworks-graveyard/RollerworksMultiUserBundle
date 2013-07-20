<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Unit\Form;

use Rollerworks\Bundle\MultiUserBundle\Form\FormFactory;

class FormFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $user = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserConfig')->disableOriginalConstructor()->getMock();
        $user->expects($this->exactly(2))->method('getFormType')->with('profile')->will($this->returnValue('fos_user_profile'));
        $user->expects($this->exactly(2))->method('getFormName')->with('profile')->will($this->returnValue('fos_user_profile_form'));
        $user->expects($this->exactly(2))->method('getFormValidationGroups')->with('profile')->will($this->returnValue(array('Profile', 'Default')));

        $userDiscriminator = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface');
        $userDiscriminator->expects($this->exactly(2))->method('getCurrentUserConfig')->will($this->returnValue($user));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory
            ->expects($this->exactly(2))
            ->method('createNamed')
            ->with('fos_user_profile_form', 'fos_user_profile', null, array('validation_groups' => array('Profile', 'Default')))
            ->will($this->returnValue('ImAForm')) // Ensure this method returns
        ;

        $factory = new FormFactory($formFactory, 'profile');
        $factory->setUserDiscriminator($userDiscriminator);

        $this->assertEquals('ImAForm', $factory->createForm());
        $this->assertEquals('ImAForm', $factory->createForm());
    }
}
