<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class RollerworksMultiUserExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('container/services.xml');
        $loader->load('container/listeners.xml');
        $loader->load('container/forms.xml');
        $loader->load('container/templating_twig.xml');
        $loader->load('container/templating_php.xml');

        $container->setParameter('rollerworks_multi_user.from_email.address', $config['from_email']['address']);
        $container->setParameter('rollerworks_multi_user.from_email.sender_name', $config['from_email']['sender_name']);

        // add some required classes for compilation
        $this->addClassesToCompile(array(
            'Rollerworks\\Bundle\\MultiUserBundle\\Model\\UserConfig',
            'Rollerworks\\Bundle\\MultiUserBundle\\Model\\UserDiscriminator',
            'Rollerworks\\Bundle\\MultiUserBundle\\EventListener\\RequestListener',
            'Rollerworks\\Bundle\\MultiUserBundle\\EventListener\\AuthenticationListener',
        ));
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $fosConfig = array(
            'db_driver' => $config['db_driver'], // Pass the driver until we have a proper fix for issue multiple drivers
            'use_listener' => $config['use_listener'],
            'firewall_name' => 'dummy',
            'user_class' => 'Acme\UserBundle\Entity\User',
            'from_email' => $config['from_email'],
            'registration' => array(
                'confirmation' => array(
                    'enabled' => false,
                )
            ),
            'service' => array(
                'mailer' => 'rollerworks_multi_user.mailer.delegating',
                'user_manager' => 'rollerworks_multi_user.user_manager.delegating',
            ),
            'group' => array(
                'group_class' => 'Acme\UserBundle\Entity\Group',
                'group_manager' => 'rollerworks_multi_user.group_manager.delegating',
             ),
        );

        /*
         * We provide the FosUserBundle with a dummy configuration to load everything.
         * Later the parent services are removed.
         */

        $container->prependExtensionConfig('fos_user', $fosConfig);
    }
}
