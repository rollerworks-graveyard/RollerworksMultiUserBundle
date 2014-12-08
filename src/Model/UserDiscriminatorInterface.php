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

use Rollerworks\Bundle\MultiUserBundle\Exception\NoActiveUserSystemException;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
interface UserDiscriminatorInterface
{
    /**
     * Adds a new user to the discriminator.
     *
     * @param string     $name
     * @param UserConfig $user
     */
    public function addUser($name, UserConfig $user);

    /**
     * Set the current user.
     *
     * @param string $name
     */
    public function setCurrentUser($name);

    /**
     * Returns the name of the current user.
     *
     * @return string|null
     */
    public function getCurrentUser();

    /**
     * Returns the configuration of the current user.
     *
     * This must throw an NoActiveUserSystemException when there is no user-system active.
     *
     * @return UserConfig
     *
     * @throws NoActiveUserSystemException when there is no user-system active
     */
    public function getCurrentUserConfig();
}
