<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailConfirmationListener implements EventSubscriberInterface
{
    private $mailer;
    private $tokenGenerator;
    private $router;
    private $session;

    /**
     * @var UserDiscriminatorInterface
     */
    private $userDiscriminator;

    public function __construct(MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, UrlGeneratorInterface $router, SessionInterface $session, UserDiscriminatorInterface $userDiscriminator)
    {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->userDiscriminator = $userDiscriminator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        if (!$this->userDiscriminator->getCurrentUserConfig()->getConfig('registering.confirmation.enabled', false)) {
            return;
        }

        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();

        $user->setEnabled(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendConfirmationEmailMessage($user);
        $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());

        $url = $this->router->generate($this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix().'_registration_check_email');
        $event->setResponse(new RedirectResponse($url));
    }
}
