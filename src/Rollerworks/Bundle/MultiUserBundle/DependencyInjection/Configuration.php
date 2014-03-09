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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $supportedDrivers = array('orm', 'mongodb', 'couchdb');

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rollerworks_multi_user');

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of: ' . implode(',', $supportedDrivers))
                    ->end()
                ->end()
                ->booleanNode('use_listener')->defaultTrue()->end()
                ->arrayNode('from_email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('address')->defaultValue('webmaster@example.com')->cannotBeEmpty()->end()
                        ->scalarNode('sender_name')->defaultValue('webmaster')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     * @param array               $enableServices array of configurations to enable: 'profile', 'change_password', 'registration', 'resetting', 'group'
     *
     * @return ArrayNodeDefinition
     *
     * @throws \InvalidArgumentException on unknown section
     *
     * @internal
     */
    final public function addUserConfig(ArrayNodeDefinition $rootNode, array $enableServices = array('profile', 'change_password', 'registration', 'resetting', 'group'))
    {
        $supportedDrivers = array('orm', 'mongodb', 'couchdb', 'custom');

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of: ' . implode(',', $supportedDrivers))
                    ->end()
                ->end()
                ->booleanNode('use_listener')->defaultTrue()->end()
                ->scalarNode('path')->defaultNull()->end()
                ->scalarNode('host')->defaultNull()->end()
                ->scalarNode('request_matcher')->defaultNull()->end()
                ->scalarNode('user_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('services_prefix')
                    ->defaultNull()
                    ->validate()
                        ->ifInArray(array('fos_user'))
                        ->thenInvalid('service_service can not be "fos_user" as its already used.')
                    ->end()
                ->end()
                ->scalarNode('routes_prefix')->defaultNull()->end()
                ->scalarNode('firewall_name')->defaultNull()->end()
                ->scalarNode('model_manager_name')->defaultValue('default')->end()
                ->booleanNode('use_username_form_type')->defaultTrue()->end()
                ->arrayNode('from_email')
                    ->children()
                        ->scalarNode('address')->defaultNull()->end()
                        ->scalarNode('sender_name')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return null === $v['request_matcher'] && null === $v['host'] && null === $v['path']; })
                ->thenInvalid('You need to specify a "request_matcher" service-id or "host" and/or url "path" for the discriminator.')
            ->end()
        ;

        $this->addSecuritySection($rootNode);
        $this->addServiceSection($rootNode);
        $this->addTemplateSection($rootNode);

        $availableSections = array('profile', 'change_password', 'registration', 'resetting', 'group');
        $camelize = function ($match) {
            return ('.' === $match[1] ? '_' : '') . strtoupper($match[2]);
        };

        foreach ($enableServices as $serviceName) {
            if (!in_array($serviceName, $availableSections)) {
                throw new \InvalidArgumentException(sprintf('Unable to add unknown configuration-section "%s".', $serviceName));
            }

            $methodName = 'add' . preg_replace_callback('/(^|_|\.)+(.)/', $camelize, $serviceName) . 'Section';
            call_user_func(array($this, $methodName), $rootNode);
        }

        return $rootNode;
    }

    final public function addSecuritySection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('login')
                            ->children()
                                ->scalarNode('template')->defaultValue('RollerworksMultiUserBundle:UserBundle/Security:login.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    // Everything below this is copied from the FOSUserBundle, and is considered @internal
    // Its only marked public for call_user_func() in addUserConfig()

    final public function addProfileSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('profile')
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('fos_user_profile')->end()
                                ->scalarNode('class')->defaultValue('FOS\UserBundle\Form\Type\ProfileFormType')->end()
                                ->scalarNode('name')->defaultValue('fos_user_profile_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('Profile', 'Default'))
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('edit')->defaultValue('RollerworksMultiUserBundle:UserBundle/Profile:edit.html.twig')->end()
                                ->scalarNode('show')->defaultValue('RollerworksMultiUserBundle:UserBundle/Profile:show.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    final public function addRegistrationSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('registration')
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('confirmation')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('template')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('email')->defaultValue('RollerworksMultiUserBundle:UserBundle/Registration:email.txt.twig')->end()
                                        ->scalarNode('confirmed')->defaultValue('RollerworksMultiUserBundle:UserBundle/Registration:confirmed.html.twig')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('from_email')
                                    ->canBeUnset()
                                    ->children()
                                        ->scalarNode('address')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('sender_name')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('fos_user_registration')->end()
                                ->scalarNode('class')->defaultValue('FOS\UserBundle\Form\Type\RegistrationFormType')->end()
                                ->scalarNode('name')->defaultValue('fos_user_registration_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('Registration', 'Default'))
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('register')->defaultValue('RollerworksMultiUserBundle:UserBundle/Registration:register.html.twig')->end()
                                ->scalarNode('check_email')->defaultValue('RollerworksMultiUserBundle:UserBundle/Registration:checkEmail.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    final public function addResettingSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('resetting')
                    ->canBeUnset()
                    ->children()
                        ->integerNode('token_ttl')->defaultValue(86400)->end()
                        ->arrayNode('email')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('from_email')
                                    ->canBeUnset()
                                    ->children()
                                        ->scalarNode('address')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('sender_name')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')->defaultNull()->end()
                                ->scalarNode('type')->defaultValue('fos_user_resetting')->end()
                                ->scalarNode('class')->defaultValue('FOS\UserBundle\Form\Type\ResettingFormType')->end()
                                ->scalarNode('name')->defaultValue('fos_user_resetting_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('ResetPassword', 'Default'))
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('check_email')->defaultValue('RollerworksMultiUserBundle:UserBundle/Resetting:checkEmail.html.twig')->end()
                                ->scalarNode('email')->defaultValue('RollerworksMultiUserBundle:UserBundle/Resetting:email.txt.twig')->end()
                                ->scalarNode('password_already_requested')->defaultValue('RollerworksMultiUserBundle:UserBundle/Resetting:passwordAlreadyRequested.html.twig')->end()
                                ->scalarNode('request')->defaultValue('RollerworksMultiUserBundle:UserBundle/Resetting:request.html.twig')->end()
                                ->scalarNode('reset')->defaultValue('RollerworksMultiUserBundle:UserBundle/Resetting:reset.html.twig')->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
            ->end()
        ;
    }

    final public function addChangePasswordSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('change_password')
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('fos_user_change_password')->end()
                                ->scalarNode('class')->defaultValue('FOS\UserBundle\Form\Type\ChangePasswordFormType')->end()
                                ->scalarNode('name')->defaultValue('fos_user_change_password_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('ChangePassword', 'Default'))
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('change_password')->defaultValue('RollerworksMultiUserBundle:UserBundle/ChangePassword:changePassword.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    final public function addServiceSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('service')
                        ->children()
                            ->scalarNode('mailer')->defaultValue('fos_user.mailer.default')->end()
                            ->scalarNode('email_canonicalizer')->defaultValue('fos_user.util.canonicalizer.default')->end()
                            ->scalarNode('username_canonicalizer')->defaultValue('fos_user.util.canonicalizer.default')->end()
                            ->scalarNode('user_manager')->defaultValue('fos_user.user_manager.default')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    final public function addTemplateSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('engine')->defaultValue('twig')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    final public function addGroupSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('group')
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('group_class')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('group_manager')->defaultValue('fos_user.group_manager.default')->end()
                        ->arrayNode('form')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('fos_user_group')->end()
                                ->scalarNode('class')->defaultValue('FOS\UserBundle\Form\Type\GroupFormType')->end()
                                ->scalarNode('name')->defaultValue('fos_user_group_form')->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(array('Registration', 'Default'))
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('edit')->defaultValue('RollerworksMultiUserBundle:UserBundle/Group:edit.html.twig')->end()
                                ->scalarNode('list')->defaultValue('RollerworksMultiUserBundle:UserBundle/Group:list.html.twig')->end()
                                ->scalarNode('new')->defaultValue('RollerworksMultiUserBundle:UserBundle/Group:new.html.twig')->end()
                                ->scalarNode('show')->defaultValue('RollerworksMultiUserBundle:UserBundle/Group:show.html.twig')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
