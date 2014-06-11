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

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChangePasswordListener implements EventSubscriberInterface
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
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => array('onChangePasswordSuccess', 1),
        );
    }

    public function onChangePasswordSuccess(FormEvent $event)
    {
        if (null === $event->getResponse()) {
            $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_profile_show');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
