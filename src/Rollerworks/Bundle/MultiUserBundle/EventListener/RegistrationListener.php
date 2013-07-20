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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

class RegistrationListener implements EventSubscriberInterface
{
    private $router;
    private $userDiscriminator;

    public function __construct(UrlGeneratorInterface $router, UserDiscriminatorInterface $userDiscriminator)
    {
        $this->router = $router;
        $this->userDiscriminator = $userDiscriminator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRM => array('onRegistrationConfirm', 1),
            FOSUserEvents::REGISTRATION_SUCCESS => array('onRegistrationSuccess', 1),
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        if (null === $event->getResponse()) {
            $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_registration_confirmed');
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function onRegistrationConfirm(GetResponseUserEvent $event)
    {
        if (null === $event->getResponse()) {
            $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() .  '_registration_confirmed');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
