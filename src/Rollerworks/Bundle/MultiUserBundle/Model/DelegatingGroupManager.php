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

use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\GroupManagerInterface;

/**
 * DelegatingGroupManager selects a GroupManager for the current user.
 *
 * Please don't use this manager as its only used for the original controllers.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class DelegatingGroupManager implements GroupManagerInterface
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

    public function getClass()
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->getClass();
    }

    public function createGroup($name)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->createGroup($name);
    }

    public function deleteGroup(GroupInterface $group)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->deleteGroup($group);
    }

    public function findGroupBy(array $criteria)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->findGroupBy($criteria);
    }

    public function findGroupByName($name)
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->findGroupByName($name);
    }

    public function findGroups()
    {
        return $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->findGroups();
    }

    public function updateGroup(GroupInterface $group)
    {
        $this->userDiscriminator->getCurrentUserConfig()->getGroupManager()->updateGroup($group);
    }
}
