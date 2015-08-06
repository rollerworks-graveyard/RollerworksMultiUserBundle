<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Stub;

use FOS\UserBundle\Model\GroupInterface;

class Group implements GroupInterface
{
    public function addRole($role)
    {
    }

    public function getId()
    {
    }

    public function getName()
    {
    }

    public function hasRole($role)
    {
    }

    public function getRoles()
    {
    }

    public function removeRole($role)
    {
    }

    public function setName($name)
    {
    }

    public function setRoles(array $roles)
    {
    }
}
