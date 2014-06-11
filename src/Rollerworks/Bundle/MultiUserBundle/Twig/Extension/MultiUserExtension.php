<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Twig\Extension;

use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class MultiUserExtension extends \Twig_Extension
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
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('rollerworks_multi_user_user', array($this, 'getCurrentUser')),
        );
    }

    /**
     * @param boolean $getName
     *
     * @return \Rollerworks\Bundle\MultiUserBundle\Model\UserConfig|string|null
     */
    public function getCurrentUser($getName = false)
    {
        if ($getName) {
            return $this->userDiscriminator->getCurrentUser();
        }

        return $this->userDiscriminator->getCurrentUserConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'rollerworks_multi_user';
    }
}
