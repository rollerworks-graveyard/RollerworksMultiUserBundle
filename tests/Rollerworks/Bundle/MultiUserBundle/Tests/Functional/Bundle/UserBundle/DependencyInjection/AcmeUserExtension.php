<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory\UserServicesFactory;

class AcmeUserExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $factory = new UserServicesFactory($container);
        $factory->create('acme_user', array(
            array(
                'path' => '^/user',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\Entity\User',
                'services_prefix' => 'acme_user',
                'routes_prefix' => 'acme_user',
                'firewall_name' => 'user',

                'security' => array(
                    'login' => array(
                        'template' => 'AcmeUserBundle:Security:login.html.twig',
                    )
                ),

                'group' => array(
                    'group_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\Entity\Group'
                ),

                'profile' => array(
                    'template' => array(
                        'edit' => 'AcmeUserBundle:Profile:edit.html.twig',
                        'show' => 'AcmeUserBundle:Profile:show.html.twig',
                    ),
                    'form' => array(
                        'type' => 'acme_user_profile',
                        'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\Form\Type\ProfileFormType',
                        'name' => 'acme_user_profile_form',
                    ),
                ),

                'registration' => array(
                    'confirmation' => array(
                        'enabled' => true,
                    ),
                    'template' => array(
                        'register' => 'AcmeUserBundle:Registration:register.html.twig',
                        'check_email' => 'AcmeUserBundle:Registration:checkEmail.html.twig',
                    ),
                    'form' => array(
                        'type' => 'acme_user_registration',
                        'class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\Form\Type\RegistrationFormType',
                        'name' => 'acme_user_registration_form',
                    ),
                ),

                'resetting' => array(
                    'template' => array(
                        'check_email' => 'AcmeUserBundle:Resetting:checkEmail.html.twig',
                        'email' => 'AcmeUserBundle:Resetting:email.txt.twig',
                        'password_already_requested' => 'AcmeUserBundle:Resetting:passwordAlreadyRequested.html.twig',
                        'request' => 'AcmeUserBundle:Resetting:request.html.twig',
                        'reset' => 'AcmeUserBundle:Resetting:reset.html.twig',
                    )
                ),

                'change_password' => array(
                    'template' => array(
                        'change_password' => 'AcmeUserBundle:ChangePassword:changePassword.html.twig',
                    )
                ),
            )
        ));
    }
}
