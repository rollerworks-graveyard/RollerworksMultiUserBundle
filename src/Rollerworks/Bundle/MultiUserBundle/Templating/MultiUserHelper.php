<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Templating;

use Rollerworks\Bundle\MultiUserBundle\Model\UserConfig;
use Symfony\Component\Templating\Helper\Helper;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class MultiUserHelper extends Helper
{
    private $userDiscriminator;

    /**
     * @param UserDiscriminatorInterface $userDiscriminator
     */
    public function __construct(UserDiscriminatorInterface $userDiscriminator)
    {
        $this->userDiscriminator = $userDiscriminator;
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return $this->userDiscriminator->getCurrentUser();
    }

    /**
     * @return UserConfig
     */
    public function getUserConfig()
    {
        return $this->userDiscriminator->getCurrentUserConfig();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUser();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'rollerworks_multi_user';
    }
}
