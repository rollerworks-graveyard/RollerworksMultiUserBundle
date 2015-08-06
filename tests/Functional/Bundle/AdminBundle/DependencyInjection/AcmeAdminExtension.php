<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\AdminBundle\DependencyInjection;

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AcmeAdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $factory = new UserServicesFactory($container);
        $factory->create('acme_admin', array(
            array(
                'path' => '^/admin', // make this configurable
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\AdminBundle\Entity\Admin',
                'services_prefix' => 'acme_admin',
                'routes_prefix' => 'acme_admin',
                'firewall_name' => 'admin',

                'group' => false,

                'security' => array(
                    'login' => array(
                        'template' => 'AcmeAdminBundle:Security:login.html.twig',
                    ),
                ),

                'profile' => array(
                    'template' => array(
                        'edit' => 'AcmeAdminBundle:Profile:edit.html.twig',
                        'show' => 'AcmeAdminBundle:Profile:show.html.twig',
                    ),
                ),

                'registration' => true === $config['remove_registration'] ? false : array(
                    'template' => array(
                        'register' => 'AcmeAdminBundle:Registration:register.html.twig',
                        'check_email' => 'AcmeAdminBundle:Registration:checkEmail.html.twig',
                    ),
                ),

                'resetting' => array(
                    'template' => array(
                        'check_email' => 'AcmeAdminBundle:Resetting:checkEmail.html.twig',
                        'email' => 'AcmeAdminBundle:Resetting:email.txt.twig',
                        'password_already_requested' => 'AcmeAdminBundle:Resetting:passwordAlreadyRequested.html.twig',
                        'request' => 'AcmeAdminBundle:Resetting:request.html.twig',
                        'reset' => 'AcmeAdminBundle:Resetting:reset.html.twig',
                    ),
                ),

                'change_password' => array(
                    'template' => array(
                        'change_password' => 'AcmeAdminBundle:ChangePassword:changePassword.html.twig',
                    ),
                ),
            ),
            $config,
        ));
    }
}
