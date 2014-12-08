<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
