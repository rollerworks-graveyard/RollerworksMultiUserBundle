<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class OverrideServiceCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testOverwrite()
    {
        $container = new ContainerBuilder();

        $container->register('fos_user.registration.form.factory', 'FOS\UserBundle\Form\Factory\FormFactory')
        ->addArgument(new Reference('form.factory'))
        ->addArgument('%fos_user.registration.form.name%')
        ->addArgument('%fos_user.registration.form.type%')
        ->addArgument('%fos_user.registration.form.validation_groups%');

        $container->register('fos_user.resetting.form.factory', 'FOS\UserBundle\Form\Factory\FormFactory')
        ->addArgument(new Reference('form.factory'))
        ->addArgument('%fos_user.resetting.form.name%')
        ->addArgument('%fos_user.resetting.form.type%')
        ->addArgument('%fos_user.resetting.form.validation_groups%');

        $container->register('fos_user.profile.form.factory', 'FOS\UserBundle\Form\Factory\FormFactory')
        ->addArgument(new Reference('form.factory'))
        ->addArgument('%fos_user.profile.form.name%')
        ->addArgument('%fos_user.profile.form.type%')
        ->addArgument('%fos_user.profile.form.validation_groups%');

        $container->register('fos_user.change_password.form.factory', 'FOS\UserBundle\Form\Factory\FormFactory')
        ->addArgument(new Reference('form.factory'))
        ->addArgument('%fos_user.change_password.form.name%')
        ->addArgument('%fos_user.change_password.form.type%')
        ->addArgument('%fos_user.change_password.form.validation_groups%');

        $container->register('fos_user.group.form.factory', 'FOS\UserBundle\Form\Factory\FormFactory')
        ->addArgument(new Reference('form.factory'))
        ->addArgument('%fos_user.group.form.name%')
        ->addArgument('%fos_user.group.form.type%')
        ->addArgument('%fos_user.group.form.validation_groups%');

        $container->setDefinition('rollerworks_multi_user.registration.form.factory', new DefinitionDecorator('rollerworks_multi_user.abstract.form.factory'))
        ->replaceArgument(1, '%rollerworks_multi_user.registration.form.name%')
        ->replaceArgument(2, '%rollerworks_multi_user.registration.form.type%')
        ->replaceArgument(3, '%rollerworks_multi_user.registration.form.validation_groups%');

        $container->setDefinition('rollerworks_multi_user.resetting.form.factory', new DefinitionDecorator('rollerworks_multi_user.abstract.form.factory'))
        ->replaceArgument(1, '%rollerworks_multi_user.resetting.form.name%')
        ->replaceArgument(2, '%rollerworks_multi_user.resetting.form.type%')
        ->replaceArgument(3, '%rollerworks_multi_user.resetting.form.validation_groups%');

        $container->setDefinition('rollerworks_multi_user.profile.form.factory', new DefinitionDecorator('rollerworks_multi_user.abstract.form.factory'))
        ->replaceArgument(1, '%rollerworks_multi_user.profile.form.name%')
        ->replaceArgument(2, '%rollerworks_multi_user.profile.form.type%')
        ->replaceArgument(3, '%rollerworks_multi_user.profile.form.validation_groups%');

        $container->setDefinition('rollerworks_multi_user.change_password.form.factory', new DefinitionDecorator('rollerworks_multi_user.abstract.form.factory'))
        ->replaceArgument(1, '%rollerworks_multi_user.change_password.form.name%')
        ->replaceArgument(2, '%rollerworks_multi_user.change_password.form.type%')
        ->replaceArgument(3, '%rollerworks_multi_user.change_password.form.validation_groups%');

        $container->setDefinition('rollerworks_multi_user.group.form.factory', new DefinitionDecorator('rollerworks_multi_user.abstract.form.factory'))
        ->replaceArgument(1, '%rollerworks_multi_user.group.form.name%')
        ->replaceArgument(2, '%rollerworks_multi_user.group.form.type%')
        ->replaceArgument(3, '%rollerworks_multi_user.group.form.validation_groups%');

        $compiler = new OverrideServiceCompilerPass();
        $compiler->process($container);

        $this->assertEquals($container->getDefinition('fos_user.registration.form.factory'), $container->getDefinition('rollerworks_multi_user.registration.form.factory'));
        $this->assertEquals($container->getDefinition('fos_user.resetting.form.factory'), $container->getDefinition('rollerworks_multi_user.resetting.form.factory'));
        $this->assertEquals($container->getDefinition('fos_user.profile.form.factory'), $container->getDefinition('rollerworks_multi_user.profile.form.factory'));
        $this->assertEquals($container->getDefinition('fos_user.change_password.form.factory'), $container->getDefinition('rollerworks_multi_user.change_password.form.factory'));
        $this->assertEquals($container->getDefinition('fos_user.group.form.factory'), $container->getDefinition('rollerworks_multi_user.group.form.factory'));
    }
}
