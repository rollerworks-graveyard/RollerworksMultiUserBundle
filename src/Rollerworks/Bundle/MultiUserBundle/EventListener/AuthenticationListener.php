<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\EventListener;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Determines the current user-object by the authentication.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var UserDiscriminatorInterface
     */
    private $userDiscriminator;

    /**
     * @var string[]
     */
    private $users;

    /**
     * @param UserDiscriminatorInterface $userDiscriminator
     */
    public function __construct(UserDiscriminatorInterface $userDiscriminator)
    {
        $this->userDiscriminator = $userDiscriminator;
    }

    /**
     * @param string $name
     * @param string $class
     */
    public function addUser($name, $class)
    {
        $this->users[$class] = $name;
    }

    /**
     * @param UserEvent $event
     */
    public function onSecurityImplicitLogin(UserEvent $event)
    {
        $this->discriminate($event->getUser());
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->discriminate($event->getAuthenticationToken()->getUser());
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => array('onSecurityImplicitLogin', 255),
            SecurityEvents::INTERACTIVE_LOGIN => array('onSecurityInteractiveLogin', 255),
        );
    }

    /**
     * @param $user
     */
    protected function discriminate($user)
    {
        $class = get_class($user);
        if (isset($this->users[$class])) {
            $this->userDiscriminator->setCurrentUser($this->users[$class]);
        }
    }
}
