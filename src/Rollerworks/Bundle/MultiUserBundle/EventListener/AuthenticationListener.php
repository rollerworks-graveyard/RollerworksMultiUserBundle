<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\EventListener;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

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
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onSecurityImplicitLogin',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
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
