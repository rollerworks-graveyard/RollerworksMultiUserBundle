<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
    const CONFIG_SECTION_PROFILE = 1;
    const CONFIG_SECTION_CHANGE_PASSWORD = 2;
    const CONFIG_SECTION_REGISTRATION = 4;
    const CONFIG_SECTION_RESETTING = 8;
    const CONFIG_SECTION_GROUP = 16;
    const CONFIG_SECTION_SECURITY = 32;

    const CONFIG_DB_DRIVER = 64;
    const CONFIG_REQUEST_MATCHER = 128;
    const CONFIG_USER_CLASS = 256;

    const CONFIG_ALL = 511;

    private static $supportedDrivers = array('orm', 'mongodb', 'couchdb', 'custom');

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
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('engine')->defaultValue('twig')->end()
                    ->end()
                ->end()
                ->booleanNode('use_listener')->defaultTrue()->end()
                ->booleanNode('use_flash_notifications')->defaultTrue()->end()
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
     *
     * @return ArrayNodeDefinition
     *
     * @throws \InvalidArgumentException on unknown section
     *
     * @internal
     */
    final public function addUserSysConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray(self::$supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of: ' . implode(',', self::$supportedDrivers))
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

        $this->addProfileSection($rootNode);
        $this->addChangePasswordSection($rootNode);
        $this->addRegistrationSection($rootNode);
        $this->addResettingSection($rootNode);
        $this->addGroupSection($rootNode);

        return $rootNode;
    }

    /**
     * Adds the user-configuration to the $rootNode.
     *
     * To specify which configurations are added use a Bitmask value.
     *
     * For example;
     *
     * Configuration::CONFIG_ALL ^ Configuration::CONFIG_SECTION_PROFILE
     * Will enable everything except profile configuration.
     *
     * Configuration::CONFIG_SECTION_PROFILE | self::CONFIG_SECTION_CHANGE_PASSWORD
     * Will enable only the profile and change-password.
     *
     * Note. firewall_name, model_manager_name, use_username_form_type, from_email are always configurable.
     * When calling this method.
     *
     * @param ArrayNodeDefinition $rootNode
     * @param array|integer       $enableServices Bitmask of enabled configurations or array of enabled-services (compatibility only)
     *                                            Accepted array values: 'profile', 'change_password', 'registration', 'resetting', 'group'
     *
     * @return ArrayNodeDefinition
     *
     * @throws \InvalidArgumentException on unknown section
     *
     * @internal
     */
    final public function addUserConfig(ArrayNodeDefinition $rootNode, $enableServices = self::CONFIG_ALL)
    {
        if (is_array($enableServices)) {
            $enableServices = $this->convertArrayToBitmask($enableServices);
        }

        $supportedDrivers = self::$supportedDrivers;

        $rootNode
            ->children()
                ->scalarNode('firewall_name')->end()
                ->scalarNode('model_manager_name')->end()
                ->booleanNode('use_username_form_type')->end()
                ->arrayNode('from_email')
                    ->children()
                        ->scalarNode('address')->end()
                        ->scalarNode('sender_name')->end()
                    ->end()
                ->end()
            ->end()
        ;

        if ($enableServices & self::CONFIG_DB_DRIVER) {
            $rootNode
                ->children()
                    ->scalarNode('db_driver')
                        ->validate()
                            ->ifTrue(function ($v) use ($supportedDrivers) { return $v !== null && !in_array($v, $supportedDrivers); })
                            ->thenInvalid('The driver %s is not supported. Please choose one of: ' . implode(',', $supportedDrivers))
                        ->end()
                    ->end()
                ->end()
            ;
        }

        if ($enableServices & self::CONFIG_REQUEST_MATCHER) {
            $rootNode
                ->children()
                    ->scalarNode('path')->end()
                    ->scalarNode('host')->end()
                    ->scalarNode('request_matcher')->end()
                ->end()
            ;
        }

        if ($enableServices & self::CONFIG_USER_CLASS) {
            $rootNode
                ->children()
                    ->scalarNode('user_class')->end()
                ->end()
            ;
        }

        if ($enableServices & self::CONFIG_SECTION_PROFILE) {
            $this->addProfileSection($rootNode, false);
        }

        if ($enableServices & self::CONFIG_SECTION_CHANGE_PASSWORD) {
            $this->addChangePasswordSection($rootNode, false);
        }

        if ($enableServices & self::CONFIG_SECTION_REGISTRATION) {
            $this->addRegistrationSection($rootNode, false);
        }

        if ($enableServices & self::CONFIG_SECTION_RESETTING) {
            $this->addResettingSection($rootNode, false);
        }

        if ($enableServices & self::CONFIG_SECTION_GROUP) {
            $this->addGroupSection($rootNode, false);
        }

        if ($enableServices & self::CONFIG_SECTION_SECURITY) {
            $this->addSecuritySection($rootNode, false);
        }

        $this->addTemplateSection($rootNode, false);

        return $rootNode;
    }

    final public function addSecuritySection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
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
        } else {
            $node
                ->children()
                    ->arrayNode('security')
                        ->children()
                            ->arrayNode('login')
                                ->children()
                                    ->scalarNode('template')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    // Everything below this is copied from the FOSUserBundle, and is considered @internal
    // Its only marked public for call_user_func() in addUserConfig()

    final public function addProfileSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
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
        } else {
            $node
                ->children()
                    ->arrayNode('profile')
                        ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                                ->children()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('class')->end()
                                    ->scalarNode('name')->end()
                                    ->arrayNode('validation_groups')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('template')
                                ->children()
                                    ->scalarNode('edit')->end()
                                    ->scalarNode('show')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    final public function addRegistrationSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
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
                                            ->scalarNode('address')->end()
                                            ->scalarNode('sender_name')->end()
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
        } else {
            $node
                ->children()
                    ->arrayNode('registration')
                        ->canBeUnset()
                        ->children()
                            ->arrayNode('confirmation')
                                ->children()
                                    ->booleanNode('enabled')->end()
                                    ->arrayNode('template')
                                        ->children()
                                            ->scalarNode('email')->end()
                                            ->scalarNode('confirmed')->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('from_email')
                                        ->canBeUnset()
                                        ->children()
                                            ->scalarNode('address')->end()
                                            ->scalarNode('sender_name')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('form')
                                ->children()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('class')->end()
                                    ->scalarNode('name')->end()
                                    ->arrayNode('validation_groups')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('template')
                                ->children()
                                    ->scalarNode('register')->end()
                                    ->scalarNode('check_email')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    final public function addResettingSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
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
                                            ->scalarNode('address')->end()
                                            ->scalarNode('sender_name')->end()
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
        } else {
            $node
                ->children()
                    ->arrayNode('resetting')
                        ->canBeUnset()
                        ->children()
                            ->integerNode('token_ttl')->end()
                            ->arrayNode('email')
                                ->children()
                                    ->arrayNode('from_email')
                                        ->canBeUnset()
                                        ->children()
                                            ->scalarNode('address')->end()
                                            ->scalarNode('sender_name')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('form')
                                ->children()
                                    ->scalarNode('template')->end()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('class')->end()
                                    ->scalarNode('name')->end()
                                    ->arrayNode('validation_groups')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('template')
                                ->children()
                                    ->scalarNode('check_email')->end()
                                    ->scalarNode('email')->end()
                                    ->scalarNode('password_already_requested')->end()
                                    ->scalarNode('request')->end()
                                    ->scalarNode('reset')->end()
                                ->end()
                            ->end()

                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    final public function addChangePasswordSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
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
        } else {
            $node
                ->children()
                    ->arrayNode('change_password')
                        ->canBeUnset()
                        ->children()
                            ->arrayNode('form')
                                ->children()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('class')->end()
                                    ->scalarNode('name')->end()
                                    ->arrayNode('validation_groups')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('template')
                                ->children()
                                    ->scalarNode('change_password')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    final public function addServiceSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
            $node
                ->children()
                    ->arrayNode('service')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('mailer')->defaultValue('fos_user.mailer.default')->end()
                            ->scalarNode('email_canonicalizer')->defaultValue('fos_user.util.canonicalizer.default')->end()
                            ->scalarNode('username_canonicalizer')->defaultValue('fos_user.util.canonicalizer.default')->end()
                            ->scalarNode('user_manager')->defaultValue('fos_user.user_manager.default')->end()
                        ->end()
                    ->end()
                ->end()
            ;
        } else {
            $node
                ->children()
                    ->arrayNode('service')
                        ->children()
                            ->scalarNode('mailer')->end()
                            ->scalarNode('email_canonicalizer')->end()
                            ->scalarNode('username_canonicalizer')->end()
                            ->scalarNode('user_manager')->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    final public function addTemplateSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
            $node
                ->children()
                    ->arrayNode('template')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('engine')->defaultValue('twig')->end()
                            ->scalarNode('layout')->defaultValue('RollerworksMultiUserBundle::layout.html.twig')->end()
                        ->end()
                    ->end()
                ->end()
            ;
        } else {
            $node
                ->children()
                    ->arrayNode('template')
                        ->children()
                            ->scalarNode('engine')->end()
                            ->scalarNode('layout')->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    final public function addGroupSection(ArrayNodeDefinition $node, $defaults = true)
    {
        if ($defaults) {
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
        } else {
            $node
                ->children()
                    ->arrayNode('group')
                        ->canBeUnset()
                        ->children()
                            ->scalarNode('group_class')->end()
                            ->scalarNode('group_manager')->end()
                            ->arrayNode('form')
                                ->children()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('class')->end()
                                    ->scalarNode('name')->end()
                                    ->arrayNode('validation_groups')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('template')
                                ->children()
                                    ->scalarNode('edit')->end()
                                    ->scalarNode('list')->end()
                                    ->scalarNode('new')->end()
                                    ->scalarNode('show')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        }
    }

    private function convertArrayToBitmask($enableServices)
    {
        $availableSections = array('profile', 'change_password', 'registration', 'resetting', 'group');
        $enableServicesMask = self::CONFIG_ALL ^ self::CONFIG_SECTION_PROFILE ^ self::CONFIG_SECTION_CHANGE_PASSWORD ^ self::CONFIG_SECTION_REGISTRATION ^ self::CONFIG_SECTION_RESETTING ^ self::CONFIG_SECTION_GROUP;

        foreach ($enableServices as $enableService) {
            if (!in_array($enableService, $availableSections)) {
                throw new \InvalidArgumentException(sprintf('Unable to add unknown configuration-section "%s".', $serviceName));
            }

            $enableServicesMask |= constant(__CLASS__ . '::CONFIG_SECTION_' . strtoupper($enableService));
        }

        $enableServices = $enableServicesMask;

        return $enableServices;
    }
}
