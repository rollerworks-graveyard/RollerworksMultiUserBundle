<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->changeService($container, 'fos_user.registration.form.factory', 'rollerworks_multi_user.registration.form.factory');
        $this->changeService($container, 'fos_user.resetting.form.factory', 'rollerworks_multi_user.resetting.form.factory');
        $this->changeService($container, 'fos_user.profile.form.factory', 'rollerworks_multi_user.profile.form.factory');
        $this->changeService($container, 'fos_user.change_password.form.factory', 'rollerworks_multi_user.change_password.form.factory');
        $this->changeService($container, 'fos_user.group.form.factory', 'rollerworks_multi_user.group.form.factory');
    }

    /**
     * @param string $serviceName
     * @param string $newServiceName
     */
    private function changeService(ContainerBuilder $container, $serviceName, $newServiceName)
    {
        if ($container->hasDefinition($serviceName) && $container->hasDefinition($newServiceName)) {
            $newService = $container->getDefinition($newServiceName);

            $container->removeDefinition($serviceName);
            $container->setDefinition($serviceName, $newService);
        }
    }
}
