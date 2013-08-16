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

use FOS\UserBundle\FOSUserEvents;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class EventDiscriminator implements EventSubscriberInterface
{
    /**
     * @var UserDiscriminatorInterface
     */
    private $userDiscriminator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserDiscriminatorInterface $userDiscriminator
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(UserDiscriminatorInterface $userDiscriminator, EventDispatcherInterface $eventDispatcher)
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->eventDispatcher = $eventDispatcher;
    }

    // dispatchers and event-subscriber list is generated using scripts/generate-events.php
    // last updated on 2014-03-06

    public function dispatchChangePasswordInitialize(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.change_password.edit.initialize', $e);
        }
    }

    public function dispatchChangePasswordSuccess(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.change_password.edit.success', $e);
        }
    }

    public function dispatchChangePasswordCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.change_password.edit.completed', $e);
        }
    }

    public function dispatchGroupCreateInitialize(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.create.initialize', $e);
        }
    }

    public function dispatchGroupCreateSuccess(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.create.success', $e);
        }
    }

    public function dispatchGroupCreateCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.create.completed', $e);
        }
    }

    public function dispatchGroupDeleteCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.delete.completed', $e);
        }
    }

    public function dispatchGroupEditInitialize(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.edit.initialize', $e);
        }
    }

    public function dispatchGroupEditSuccess(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.edit.success', $e);
        }
    }

    public function dispatchGroupEditCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.group.edit.completed', $e);
        }
    }

    public function dispatchProfileEditInitialize(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.profile.edit.initialize', $e);
        }
    }

    public function dispatchProfileEditSuccess(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.profile.edit.success', $e);
        }
    }

    public function dispatchProfileEditCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.profile.edit.completed', $e);
        }
    }

    public function dispatchRegistrationInitialize(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.registration.initialize', $e);
        }
    }

    public function dispatchRegistrationSuccess(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.registration.success', $e);
        }
    }

    public function dispatchRegistrationCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.registration.completed', $e);
        }
    }

    public function dispatchRegistrationConfirm(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.registration.confirm', $e);
        }
    }

    public function dispatchRegistrationConfirmed(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.registration.confirmed', $e);
        }
    }

    public function dispatchResettingResetInitialize(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.resetting.reset.initialize', $e);
        }
    }

    public function dispatchResettingResetSuccess(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.resetting.reset.success', $e);
        }
    }

    public function dispatchResettingResetCompleted(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.resetting.reset.completed', $e);
        }
    }

    public function dispatchSecurityImplicitLogin(Event $e)
    {
        if ($userSys = $this->userDiscriminator->getCurrentUser()) {
            $this->eventDispatcher->dispatch($userSys . '.security.implicit_login', $e);
        }
    }

    /**
     * Subscribes to all events defined in FOS\UserBundle\FOSUserEvents.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_INITIALIZE => 'dispatchChangePasswordInitialize',
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'dispatchChangePasswordSuccess',
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED => 'dispatchChangePasswordCompleted',
            FOSUserEvents::GROUP_CREATE_INITIALIZE => 'dispatchGroupCreateInitialize',
            FOSUserEvents::GROUP_CREATE_SUCCESS => 'dispatchGroupCreateSuccess',
            FOSUserEvents::GROUP_CREATE_COMPLETED => 'dispatchGroupCreateCompleted',
            FOSUserEvents::GROUP_DELETE_COMPLETED => 'dispatchGroupDeleteCompleted',
            FOSUserEvents::GROUP_EDIT_INITIALIZE => 'dispatchGroupEditInitialize',
            FOSUserEvents::GROUP_EDIT_SUCCESS => 'dispatchGroupEditSuccess',
            FOSUserEvents::GROUP_EDIT_COMPLETED => 'dispatchGroupEditCompleted',
            FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'dispatchProfileEditInitialize',
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'dispatchProfileEditSuccess',
            FOSUserEvents::PROFILE_EDIT_COMPLETED => 'dispatchProfileEditCompleted',
            FOSUserEvents::REGISTRATION_INITIALIZE => 'dispatchRegistrationInitialize',
            FOSUserEvents::REGISTRATION_SUCCESS => 'dispatchRegistrationSuccess',
            FOSUserEvents::REGISTRATION_COMPLETED => 'dispatchRegistrationCompleted',
            FOSUserEvents::REGISTRATION_CONFIRM => 'dispatchRegistrationConfirm',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'dispatchRegistrationConfirmed',
            FOSUserEvents::RESETTING_RESET_INITIALIZE => 'dispatchResettingResetInitialize',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'dispatchResettingResetSuccess',
            FOSUserEvents::RESETTING_RESET_COMPLETED => 'dispatchResettingResetCompleted',
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'dispatchSecurityImplicitLogin',
        );
    }
}
