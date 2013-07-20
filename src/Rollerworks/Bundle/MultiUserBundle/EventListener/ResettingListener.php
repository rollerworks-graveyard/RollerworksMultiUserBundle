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
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

class ResettingListener implements EventSubscriberInterface
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
            FOSUserEvents::RESETTING_RESET_INITIALIZE => array('onResettingResetInitialize', 1),
            FOSUserEvents::RESETTING_RESET_SUCCESS => array('onResettingResetSuccess', 1),
        );
    }

    public function onResettingResetInitialize(GetResponseUserEvent $event)
    {
        $user = $this->userDiscriminator->getCurrentUserConfig();

        $tokenTtl = $user->getConfig('resetting.token_ttl', 86400);

        if (!$event->getUser()->isPasswordRequestNonExpired($tokenTtl)) {
            $event->setResponse(new RedirectResponse($this->router->generate($user->getRoutePrefix() .  '_resetting_request')));

            // Prevent the FOSUserBundle from overwriting
            $event->stopPropagation();
        }
    }

    public function onResettingResetSuccess(FormEvent $event)
    {
        $event->setResponse(new RedirectResponse($this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() .  '_profile_show')));
    }
}
