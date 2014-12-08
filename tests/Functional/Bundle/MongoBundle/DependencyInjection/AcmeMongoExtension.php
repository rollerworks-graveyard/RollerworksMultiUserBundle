<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\MongoBundle\DependencyInjection;

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AcmeMongoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $factory = new UserServicesFactory($container);
        $factory->create('acme_mongo', array(
            array(
                'path' => '^/mongo',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\MongoBundle\Document\User',
                'services_prefix' => 'acme_mongo',
                'routes_prefix' => 'acme_mongo',
                'firewall_name' => 'mongo',
                'db_driver' => 'mongodb',

                'group' => false,

                'security' => array(
                    'login' => array(
                        'template' => 'AcmeMongoBundle:Security:login.html.twig',
                    ),
                ),

                'registration' => array(
                    'template' => array(
                        'register' => 'AcmeMongoBundle:Registration:register.html.twig',
                        'check_email' => 'AcmeMongoBundle:Registration:checkEmail.html.twig',
                    ),
                    'form' => array(
                        'type' => 'acme_mongo_registration',
                        'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\MongoBundle\Form\Type\RegistrationFormType',
                    ),
                ),

            ),
        ));
    }
}
