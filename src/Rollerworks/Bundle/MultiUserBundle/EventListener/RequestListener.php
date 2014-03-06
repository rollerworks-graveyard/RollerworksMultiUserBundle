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
