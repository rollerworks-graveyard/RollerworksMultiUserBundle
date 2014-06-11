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

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

/**
 * Tries to determine the current user-system.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class RequestListener
{
    /**
     * @var UserDiscriminatorInterface
     */
    protected $userDiscriminator;

    /**
     * @var RequestMatcherInterface[]
     */
    protected $users;

    /**
     * @param UserDiscriminatorInterface $userDiscriminator
     */
    public function __construct(UserDiscriminatorInterface $userDiscriminator)
    {
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

        $request = $event->getRequest();
        foreach ($this->users as $name => $requestMatcher) {
            if ($requestMatcher->matches($request)) {
                $this->userDiscriminator->setCurrentUser($name);

                return;
            }
        }
    }
}
