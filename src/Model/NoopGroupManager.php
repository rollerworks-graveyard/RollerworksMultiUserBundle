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

use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\GroupManagerInterface;

/**
 * NoopGroupManager, does nothing.
 *
 * Use this group manager as default when no group managing is used.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class NoopGroupManager implements GroupManagerInterface
{
    public function getClass()
    {
        // noop
    }

    public function createGroup($name)
    {
        // noop
    }

    public function deleteGroup(GroupInterface $group)
    {
        // noop
    }

    public function findGroupBy(array $criteria)
    {
        // noop
    }

    public function findGroupByName($name)
    {
        // noop
    }

    public function findGroups()
    {
        // noop
    }

    public function updateGroup(GroupInterface $group)
    {
        // noop
    }
}
