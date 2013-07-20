<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
