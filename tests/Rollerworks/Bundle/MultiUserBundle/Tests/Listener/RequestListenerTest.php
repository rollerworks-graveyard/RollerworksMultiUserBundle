<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Unit\Listener;

use Symfony\Component\HttpFoundation\Request;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminator;
use Rollerworks\Bundle\MultiUserBundle\EventListener\RequestListener;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userDiscriminator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    public function testSet()
    {
        $matcher1 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher1->expects($this->once())->method('matches')->will($this->returnValue(false));

        $matcher2 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher2->expects($this->once())->method('matches')->will($this->returnValue(true));

        $this->listener->addUser('user1', $matcher1);
        $this->listener->addUser('user2', $matcher2);

        $request = new Request();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequest')->will($this->returnValue($request));

        $this->userDiscriminator->expects($this->once())->method('setCurrentUser')->with('user2');
        $this->listener->onKernelRequest($event);
    }

    public function testSecondCall()
    {
        $matcher1 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher1->expects($this->atLeastOnce())->method('matches')->will($this->returnValue(false));

        $matcher2 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher2->expects($this->atLeastOnce())->method('matches')->will($this->returnValue(true));

        $this->listener->addUser('user1', $matcher1);
        $this->listener->addUser('user2', $matcher2);

        $request = new Request();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue($request));

        $this->userDiscriminator->expects($this->once())->method('setCurrentUser')->with('user2');
        $this->userDiscriminator->expects($this->any())->method('getCurrentUser')->will($this->onConsecutiveCalls(null, 'user2'));

        $this->listener->onKernelRequest($event);
        $this->listener->onKernelRequest($event);
    }

    public function testAlreadySet()
    {
        $matcher1 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher1->expects($this->never())->method('matches')->will($this->returnValue(false));

        $matcher2 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher2->expects($this->never())->method('matches')->will($this->returnValue(true));

        $this->listener->addUser('user1', $matcher1);
        $this->listener->addUser('user2', $matcher2);

        $request = new Request();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->never())->method('getRequest')->will($this->returnValue($request));

        $this->userDiscriminator->expects($this->never())->method('setCurrentUser');
        $this->userDiscriminator->expects($this->any())->method('getCurrentUser')->will($this->onConsecutiveCalls(null, 'user2'));

        $this->userDiscriminator->getCurrentUser();
        $this->listener->onKernelRequest($event);
    }

    public function testAlreadySetBySession()
    {
        $this->session->expects($this->once())->method('get')->with(UserDiscriminator::SESSION_NAME)->will($this->returnValue('user2'));

        $matcher1 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher1->expects($this->never())->method('matches')->will($this->returnValue(false));

        $matcher2 = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $matcher2->expects($this->never())->method('matches')->will($this->returnValue(true));

        $this->listener->addUser('user1', $matcher1);
        $this->listener->addUser('user2', $matcher2);

        $request = new Request();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->never())->method('getRequest')->will($this->returnValue($request));

        $this->userDiscriminator->expects($this->once())->method('setCurrentUser')->with('user2');
        $this->userDiscriminator->expects($this->any())->method('getCurrentUser')->will($this->returnValue(null));

        $this->listener->onKernelRequest($event);
    }

    protected function setUp()
    {
        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
                ->getMock();

        $this->userDiscriminator = $this->getMockBuilder('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();

        $this->listener = new RequestListener($this->session, $this->userDiscriminator);
    }

}
