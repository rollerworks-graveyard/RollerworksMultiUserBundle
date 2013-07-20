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
     * @return UserConfig|null
     */
    public function getCurrentUserConfig();
}
