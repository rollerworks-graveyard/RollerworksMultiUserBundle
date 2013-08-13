<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Model;

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
     * @param SessionInterface $session
     * @param UserConfig[]     $users
     */
    public function __construct(array $users = null)
    {
        if ($users) {
            foreach ($users as $name => $user) {
                $this->addUser($name, $user);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($name, UserConfig $user)
    {
        $this->users[$name] = $user;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUserConfig()
    {
        if (!isset($this->users[$this->currentUser])) {
            return null;
        }

        return $this->users[$this->currentUser];
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentUser($name)
    {
        if (!isset($this->users[$name])) {
            throw new \LogicException(sprintf('Impossible to set the user discriminator, because "%s" is not present in the users list.', $name));
        }

        $this->currentUser = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser()
    {
        if (!isset($this->users[$this->currentUser])) {
            return null;
        }

        return $this->currentUser;
    }
}
