<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Listener;

use Rollerworks\Bundle\MultiUserBundle\EventListener\AuthenticationListener;
use Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User;

class AuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userDiscriminator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $interactiveLoginEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $implicitLoginEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var AuthenticationListener
     */
    protected $listener;

    public function testOnSecurityInteractiveLogin()
    {
        $this->interactiveLoginEvent->expects($this->once())->method('getAuthenticationToken')->will($this->returnValue($this->token));
        $this->token->expects($this->once())->method('getUser')->will($this->returnValue($this->user));
        $this->userDiscriminator->expects($this->exactly(1))->method('setCurrentUser')->with('acme');

        $this->listener->onSecurityInteractiveLogin($this->interactiveLoginEvent);
    }

    public function testOnSecurityImplicitLogin()
    {
        $this->implicitLoginEvent->expects($this->once())->method('getUser')->will($this->returnValue($this->user));
        $this->userDiscriminator->expects($this->exactly(1))->method('setCurrentUser')->with('acme');

        $this->listener->onSecurityImplicitLogin($this->implicitLoginEvent);
    }

    protected function setUp()
    {
        $this->userDiscriminator = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();

        $this->interactiveLoginEvent = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')
                ->disableOriginalConstructor()->getMock();

        $this->implicitLoginEvent = $this->getMockBuilder('FOS\UserBundle\Event\UserEvent')
                ->disableOriginalConstructor()->getMock();

        $this->token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken')
                ->disableOriginalConstructor()->getMock();

        $this->user = new User();

        $this->listener = new AuthenticationListener($this->userDiscriminator);
        $this->listener->addUser('acme', 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User');
    }
}
