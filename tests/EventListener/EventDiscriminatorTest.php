<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\EventListener;

use Rollerworks\Bundle\MultiUserBundle\EventListener\EventDiscriminator;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDiscriminatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserDiscriminatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userDiscriminator;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    private static $events;

    /**
     * @dataProvider getEvents
     */
    public function testDispatchEventWithActiveUserSystem($fosEvent, $method, $eventName)
    {
        $this->userDiscriminator->expects($this->once())
            ->method('getCurrentUser')
            ->will($this->returnValue('acme_user'));

        $my = $this;
        $subscriber = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Tests\EventListener\MyEventSubscriber');
        $subscriber->expects($this->once())
            ->method('myFunc')
            ->with(
                $this->isInstanceOf('Symfony\Component\EventDispatcher\Event')
            )
            ->will($this->onConsecutiveCalls(function ($e) use ($my, $method) {
                $my->assertInstanceOf('Rollerworks\Bundle\MultiUserBundle\Tests\EventListener\Event', $e);
                $my->assertEquals($method, $e->getMethod());
            }));

        $this->eventDispatcher->addSubscriber(new EventDiscriminator($this->userDiscriminator, $this->eventDispatcher));
        $this->eventDispatcher->addListener($eventName, array($subscriber, 'myFunc'));
        $this->eventDispatcher->dispatch($fosEvent, new Event($method));
    }

    /**
     * @dataProvider getEvents
     */
    public function testDispatcherOnlyForActive($fosEvent, $method, $eventName)
    {
        $this->userDiscriminator->expects($this->once())
            ->method('getCurrentUser')
            ->will($this->returnValue('acme_admin'));

        $subscriber = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Tests\EventListener\MyEventSubscriber');
        $subscriber->expects($this->never())->method('myFunc');

        $this->eventDispatcher->addSubscriber(new EventDiscriminator($this->userDiscriminator, $this->eventDispatcher));
        $this->eventDispatcher->addListener($eventName, array($subscriber, 'myFunc'));
        $this->eventDispatcher->dispatch($fosEvent, new Event($method));
    }

    /**
     * @dataProvider getEvents
     */
    public function testDoesNotDispatcherWhenNoUserSysIsActive($fosEvent, $method, $eventName)
    {
        $subscriber = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Tests\EventListener\MyEventSubscriber');
        $subscriber->expects($this->never())->method('myFunc');

        $this->eventDispatcher->addSubscriber(new EventDiscriminator($this->userDiscriminator, $this->eventDispatcher));
        $this->eventDispatcher->addListener($eventName, array($subscriber, 'myFunc'));
        $this->eventDispatcher->dispatch($fosEvent, new Event($method));
    }

    public static function getEvents()
    {
        if (null !== self::$events) {
            return self::$events;
        }

        $eventsClass = new \ReflectionClass('FOS\UserBundle\FOSUserEvents');
        $events = $eventsClass->getConstants();
        $finalEvents = array();

        foreach ($events as $event => $eventValue) {
            $finalEvents[] = array(
                $eventValue,
                ucfirst(self::underscoreToCamelCase($event)),
                'acme_user'.substr($eventValue, 8),
            );
        }

        self::$events = $finalEvents;

        return $finalEvents;
    }

    protected function setUp()
    {
        $this->userDiscriminator = $this->getMock('Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface');
        $this->eventDispatcher = new EventDispatcher();
    }

    protected static function underscoreToCamelCase($string)
    {
        $string = strtolower($string);

        return preg_replace_callback('/_([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, $string);
    }
}

interface MyEventSubscriber
{
    public function myFunc($event);
}

class Event extends BaseEvent
{
    private $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }
}
