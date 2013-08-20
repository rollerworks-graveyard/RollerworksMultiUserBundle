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

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupListener implements EventSubscriberInterface
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
            FOSUserEvents::GROUP_CREATE_SUCCESS => array('onGroupCreateSuccess', 1),
            FOSUserEvents::GROUP_EDIT_SUCCESS => array('onGroupEditSuccess', 1),
            FOSUserEvents::GROUP_DELETE_COMPLETED => array('onGroupDeleteCompleted', 1),
        );
    }

    public function onGroupCreateSuccess(FormEvent $event)
    {
        if (null === $event->getResponse()) {
            $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_group_show', array('groupName' => $event->getForm()->getData()->getName()));
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function onGroupEditSuccess(FormEvent $event)
    {
        if (null === $event->getResponse()) {
            $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_group_show', array('groupName' => $event->getForm()->getData()->getName()));
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function onGroupDeleteCompleted(FormEvent $event)
    {
        if ($event->getResponse() instanceof RedirectResponse) {
            $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_group_list');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
