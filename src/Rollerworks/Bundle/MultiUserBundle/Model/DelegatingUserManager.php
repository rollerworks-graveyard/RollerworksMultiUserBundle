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

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * DelegatingUserManager selects a UserManager for the current user.
 *
 * Please don't use this manager as its only used for the original controllers.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class DelegatingUserManager implements UserManagerInterface
{
    private $userDiscriminator;

    public function __construct(UserDiscriminatorInterface $userDiscriminator)
    {
        $this->userDiscriminator = $userDiscriminator;
    }

    /**
     * @return UserDiscriminatorInterface
     */
    public function getUserDiscriminator()
    {
        return $this->userDiscriminator;
    }

    public function createUser()
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->createUser();
    }

    public function deleteUser(UserInterface $user)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->deleteUser($user);
    }

    public function findUserBy(array $criteria)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->findUserBy($criteria);
    }

    public function findUserByUsername($username)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->findUserByUsername($username);
    }

    public function findUserByEmail($email)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->findUserByEmail($email);
    }

    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->findUserByUsernameOrEmail($usernameOrEmail);
    }

    public function findUserByConfirmationToken($token)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->findUserByConfirmationToken($token);
    }

    public function findUsers()
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->findUsers();
    }

    public function getClass()
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->getClass();
    }

    public function reloadUser(UserInterface $user)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->reloadUser($user);
    }

    public function updateUser(UserInterface $user)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->updateUser($user);
    }

    public function updateCanonicalFields(UserInterface $user)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->updateCanonicalFields($user);
    }

    public function updatePassword(UserInterface $user)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getUserManager()->updatePassword($user);
    }
}
