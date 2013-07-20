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

    private function changeService(ContainerBuilder $container, $serviceName, $newServiceName)
    {
        if ($container->hasDefinition($serviceName) && $container->hasDefinition($newServiceName)) {
            $newService = $container->getDefinition($newServiceName);

            $container->removeDefinition($serviceName);
            $container->setDefinition($serviceName, $newService);
        }
    }
}
