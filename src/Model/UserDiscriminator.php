<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Model;

use Rollerworks\Bundle\MultiUserBundle\Exception\NoActiveUserSystemException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class UserDiscriminator implements UserDiscriminatorInterface
{
    /**
     * @var UserConfig[]
     */
    protected $users = array();

    /**
     * @var string
     */
    protected $currentUser = null;

    /**
     * @param UserConfig[] $users
     */
    public function __construct(array $users = null)
    {
        if ($users) {
            foreach ($users as $name => $user) {
                if (!$user instanceof UserConfig) {
                    throw new UnexpectedTypeException($user, 'Rollerworks\Bundle\MultiUserBundle\Model\UserConfig');
                }

                $this->users[$name] = $user;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addUser($name, UserConfig $user)
    {
        $this->users[$name] = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUserConfig()
    {
        if (!$this->currentUser) {
            throw new NoActiveUserSystemException('Unable to get UserConfig, because there is no user-system active. Please call setCurrentUser() to activate a user-system or refer to the user-system services directly.');
        }

        return $this->users[$this->currentUser];
    }

    /**
     * {@inheritdoc}
     */
    public function hasCurrentUserConfig()
    {
        return null !== $this->currentUser;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentUser($name)
    {
        if (!isset($this->users[$name])) {
            throw new \LogicException(sprintf('Impossible to set the user discriminator, because "%s" is not present in the users list.', $name));
        }

        $this->currentUser = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }
}
