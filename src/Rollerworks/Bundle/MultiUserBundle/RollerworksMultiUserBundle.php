<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler\RegisterUserPass;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler\RemoveParentServicesPass;

/**
 * RollerworksMultiUserBundle.
 *
 * Provides user management functionality (authentication, authorization, etc).
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class RollerworksMultiUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new RemoveParentServicesPass());
        $container->addCompilerPass(new RegisterUserPass());
    }

    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
