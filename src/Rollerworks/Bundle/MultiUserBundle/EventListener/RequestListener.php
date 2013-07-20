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

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminator;

/**
 * Tries to determine the current user-system.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class RequestListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var UserDiscriminatorInterface
     */
    protected $userDiscriminator;

    /**
     * @var RequestMatcherInterface[]
     */
    protected $users;

    /**
     * @param SessionInterface           $session
     * @param UserDiscriminatorInterface $userDiscriminator
     */
    public function __construct(SessionInterface $session, UserDiscriminatorInterface $userDiscriminator)
    {
        $this->session = $session;
        $this->userDiscriminator = $userDiscriminator;
        $this->users = array();
    }

    /**
     * @param string                  $name
     * @param RequestMatcherInterface $requestMatcher
     */
    public function addUser($name, RequestMatcherInterface $requestMatcher)
    {
        $this->users[$name] = $requestMatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Already set
        if (null !== $this->userDiscriminator->getCurrentUser()) {
            return;
        }

        if ($name = $this->session->get(UserDiscriminator::SESSION_NAME)) {
            $this->userDiscriminator->setCurrentUser($name);

            return;
        }

        $request = $event->getRequest();
        foreach ($this->users as $name => $requestMatcher) {
            if ($requestMatcher->matches($request)) {
                $this->userDiscriminator->setCurrentUser($name);

                return;
            }
        }
    }
}
