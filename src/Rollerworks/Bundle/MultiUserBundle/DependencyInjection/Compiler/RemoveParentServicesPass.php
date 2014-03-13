<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * RemoveParentServicesPass, marks the parent 'FOSUserBundle' services as abstract.
 *
 * By making them abstract they are removed and prevent any conflict or container bloat.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * @codeCoverageIgnore
 */
class RemoveParentServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('fos_user.listener.authentication')->setAbstract(true)->clearTags();
        $container->getDefinition('fos_user.listener.resetting')->setAbstract(true)->clearTags();

        // Forms
        $container->getDefinition('fos_user.registration.form.type')->setAbstract(true);
        $container->getDefinition('fos_user.resetting.form.type')->setAbstract(true);
        $container->getDefinition('fos_user.profile.form.type')->setAbstract(true);
        $container->getDefinition('fos_user.change_password.form.type')->setAbstract(true);
        $container->getDefinition('fos_user.group.form.type')->setAbstract(true);
    }
}
