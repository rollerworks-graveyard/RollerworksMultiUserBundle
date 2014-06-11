<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Factory;

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Register User manager services.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
class UserServicesFactory
{
    protected $container;
    protected $servicePrefix;
    protected $routesPrefix;

    /**
     * @var TreeBuilder
     */
    private static $configTree;

    /**
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;

        // Keep it static to prevent to many objects
        if (!self::$configTree) {
            $configTree = new TreeBuilder();

            $node = $configTree->root('user');
            $configuration = new Configuration();
            $configuration->addUserSysConfig($node);

            self::$configTree = $configTree;
        }
    }

    /**
     * @param string $name
     * @param array  $config
     */
    public function create($name, array $config)
    {
        $knownOptions = array(
            'db_driver', 'path', 'host', 'request_matcher', 'user_class', 'services_prefix', 'routes_prefix',
            'firewall_name', 'model_manager_name', 'use_username_form_type', 'from_email', 'security', 'profile', 'change_password',
            'registration', 'resetting', 'group', 'service', 'use_listener', 'template',
        );

        // Strip unknown configuration keys, so that the user-configuration can be kept at root level
        // With custom configurations added
        foreach ($config as $level => $conf) {
            foreach (array_keys($conf) as $n) {
                if (!in_array($n, $knownOptions, true)) {
                    unset($config[$level][$n]);
                }
            }
        }

        // Ensure this always exists
        array_unshift($config, array(
            'from_email' => array(
                'address' => '%rollerworks_multi_user.from_email.address%',
                'sender_name' => '%rollerworks_multi_user.from_email.sender_name%',
            )
        ));

        // Ensure the service section is always set
        // This can not be done automatically because then the app-config
        // will overwrite it with the defaults again.
        if (!isset($config[0]['service'])) {
            $config[0]['service'] = array();
        }

        $config = $this->processConfiguration($config);

        $this->servicePrefix = ($config['services_prefix'] ?: $name);
        $this->routesPrefix = null !== $config['routes_prefix'] ? $config['routes_prefix'] : $name;

        $user = $this->createUserConfig($this->container, $name, $config);

        $this->remapParametersNamespaces($config, $this->container, array(
            '' => array(
                //'db_driver' => $this->servicePrefix . '.storage',
                'firewall_name' => $this->servicePrefix . '.firewall_name',
                'model_manager_name' => $this->servicePrefix . '.model_manager_name',
                'user_class' => $this->servicePrefix . '.model.user.class',
            ),
            'template' => $this->servicePrefix . '.template.%s',
        ));

        $this->loadMailer($config, $this->container);
        $this->loadServices($config, $this->container);

        if (!empty($config['profile'])) {
            $this->loadProfile($config['profile'], $this->container, $user);
        }

        if (!empty($config['security'])) {
            $this->loadSecurity($config['security'], $this->container, $user);
        }

        if (!empty($config['registration'])) {
            $this->loadRegistration($config['registration'], $this->container, $user, $config['from_email']);
        }

        if (!empty($config['change_password'])) {
            $this->loadChangePassword($config['change_password'], $this->container, $user);
        }

        if (!empty($config['resetting'])) {
            $this->loadResetting($config['resetting'], $this->container, $user, $config['from_email']);
        }

        if (!empty($config['group'])) {
            $this->loadGroups($config['group'], $this->container, $user);
        } else {
            // Always do this prevent undefined methods
            $this->container->setAlias(sprintf('%s.group_manager', $this->servicePrefix), 'rollerworks_multi_user.group_manager.noop');
        }

        $this->ensureParameterSet(sprintf('%s.registration.confirmation.email.template', $this->servicePrefix), 'Please configure registration properly');
        $this->ensureParameterSet(sprintf('%s.resetting.email.template', $this->servicePrefix), 'Please configure resetting properly');
        $this->ensureParameterSet(sprintf('%s.registration.confirmation.from_email', $this->servicePrefix), $config['from_email']);
        $this->ensureParameterSet(sprintf('%s.resetting.email.from_email', $this->servicePrefix), $config['from_email']);

        if ('db_driver' !== $config['db_driver']) {
            $this->container->setParameter('fos_user.backend_type_' . $config['db_driver'], true);
        }
    }

    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (array_key_exists($name, $config)) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }

    /**
     * Ensure the parameter is set, or set the $value otherwise.
     *
     * @param string $name
     * @param mixed  $value
     */
    protected function ensureParameterSet($name, $value)
    {
        if (!$this->container->getParameterBag()->has($name)) {
            $this->container->getParameterBag()->set($name, $value);
        }
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    final protected function processConfiguration(array $configs)
    {
        $processor = new Processor();

        return $processor->process(self::$configTree->buildTree(), $configs);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $type
     * @param array            $config
     * @param Definition       $user
     * @param string           $modelRef  Reference name of the model, either 'user' or 'group'
     */
    protected function createFormService(ContainerBuilder $container, $type, array $config, Definition $user, $modelRef = null)
    {
        // Register the form-factory, this is only to be used for user-specific forms
        if ('resetting' !== $type) {
            $container->setDefinition(sprintf('%s.%s.form.factory', $this->servicePrefix, $type), new DefinitionDecorator('rollerworks_multi_user.abstract.form.factory'))
            ->replaceArgument(1, sprintf('%%%s.%s.form.name%%', $this->servicePrefix, $type))
            ->replaceArgument(2, sprintf('%%%s.%s.form.type%%', $this->servicePrefix, $type))
            ->replaceArgument(3, sprintf('%%%s.%s.form.validation_groups%%', $this->servicePrefix, $type))
            ;
        }

        // Only register the form type when a class is set, this allows for a custom type service
        if (null !== $config['form']['class']) {
            $formType = $container->setDefinition(sprintf('%s.%s.form.type', $this->servicePrefix, $type), new DefinitionDecorator(sprintf('fos_user.%s.form.type', $type)))
            ->setClass($config['form']['class'])
            ->setTags(array('form.type' => array(array('alias' => $config['form']['type']))));

            if ($modelRef) {
                $formType->replaceArgument(0, sprintf('%%%s.model.%s.class%%', $this->servicePrefix, $modelRef));
            }
        }

        $user->addMethodCall('setForm', array(
            $type,
            sprintf('%%%s.%s.form.name%%', $this->servicePrefix, $type),
            sprintf('%%%s.%s.form.type%%', $this->servicePrefix, $type),
            sprintf('%%%s.%s.form.validation_groups%%', $this->servicePrefix, $type)
        ));
    }

    protected function convertFormType(ContainerBuilder $container, $type, array $config, $modelRef = null)
    {
        if ('fos_user_' . $type === $config['type'] && 'FOS\\UserBundle\\Form\\Type\\' !== substr($config['class'], 0, 25)) {
            throw new \RuntimeException(sprintf('Form type "%s" uses the "fos_user_" prefix with a custom class. Please overwrite the getName() method to return a unique name.', $config['type']));
        }

        if ('fos_user_' . $type === $config['type'] || 'FOS\\UserBundle\\Form\\Type\\' === substr($config['class'], 0, 25)) {
            $config['type'] = sprintf('%s_%s',  $this->servicePrefix, $type);
            $config['class'] = 'Rollerworks\\Bundle\\MultiUserBundle\Form\\Type\\' . join('', array_slice(explode('\\', $config['class']), -1));

            $container->setDefinition(sprintf('%s.%s.form.type', $this->servicePrefix, $type), new Definition($config['class']))
                ->setTags(array('form.type' => array(array('alias' => $config['type']))))
                ->addArgument(sprintf('%%%s.model.%s.class%%', $this->servicePrefix, $modelRef))
                ->addArgument($config['type']);

            $config['class'] = null;
        }

        if ('fos_user_' . $type . '_form' === $config['name']) {
            $config['name'] = sprintf('%s_%s_form',  $this->servicePrefix, $type);
        }

        return $config;
    }

    /**
     * @param string     $section
     * @param array      $config
     * @param Definition $user
     */
    protected function setTemplates($section, array $config, Definition $user)
    {
        if (is_array($config['template'])) {
            foreach ($config['template'] as $name => $resource) {
                $user->addMethodCall('setTemplate', array(
                    sprintf('%s.%s', $section, $name),
                    sprintf('%%%s.%s.%s.template%%', $this->servicePrefix, $section, $name),
                ));
            }
        } else {
            $user->addMethodCall('setTemplate', array(
                $section,
                sprintf('%%%s.%s.template%%', $this->servicePrefix, $section)
            ));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $config
     *
     * @return Definition
     */
    protected function createUserConfig(ContainerBuilder $container, $name, array $config)
    {
        $tagParams = array(
            'alias' => $name,
            'class' => $config['user_class'],
            'path' => $config['path'],
            'host' => $config['host'],
            'db_driver' => $config['db_driver'],
        );

        if (null !== $config['request_matcher']) {
            $tagParams['request_matcher'] = $config['request_matcher'];
        }

        $def = $container->register('rollerworks_multi_user.user_system.' . $name, 'Rollerworks\Bundle\MultiUserBundle\Model\UserConfig');
        $def
            ->addArgument($this->servicePrefix)
            ->addArgument($this->routesPrefix)
            ->addArgument(new Reference(sprintf('%s.user_manager', $this->servicePrefix)))
            ->addArgument(new Reference(sprintf('%s.group_manager', $this->servicePrefix)))
            ->addMethodCall('setConfig', array('use_listener', $config['use_listener']))
            ->setPublic(false)
            ->addTag('rollerworks_multi_user.user_system', $tagParams)
        ;

        $def->addMethodCall('setTemplate', array('layout', $config['template']['layout']));

        if (version_compare(Kernel::VERSION, '2.3.0', '>=')) {
            $def->setLazy(true);
        }

        return $def;
    }

    protected function loadModelManager(array $config, ContainerBuilder $container)
    {
        $serviceName = sprintf('rollerworks_multi_user.%s.model_manager', $this->servicePrefix);

        if (!$container->hasDefinition($serviceName)) {
            $service = null;
            $class = null;

            switch ($config['db_driver']) {
                case 'orm':
                    $service = 'doctrine';
                    $class = 'Doctrine\ORM\EntityManager';
                    break;

                case 'mongodb':
                    $service = 'doctrine_mongodb';
                    $class = 'Doctrine\ODM\MongoDB\DocumentManager';
                    break;

                case 'couchdb':
                    $service = 'doctrine_couchdb';
                    $class = 'Doctrine\ODM\CouchDB\DocumentManager';
                    break;
            }

            $container->setDefinition($serviceName, new Definition($class))
            ->setFactoryService($service)
            ->setFactoryMethod('getManager')
            ->setPublic(false)
            ->setArguments(array($config['model_manager_name']));

            // Note. Its no issue to set the listener multiple times
            // Symfony will just overwrite them, each listener is unique per db-driver
            $this->registerUserListener($container, $config['db_driver']);
        }

        return $serviceName;
    }

    private function registerUserListener(ContainerBuilder $container, $dbDriver)
    {
        $listenerService = null;

        switch ($dbDriver) {
            case 'orm':
                $listenerService = new Definition('Rollerworks\\Bundle\\MultiUserBundle\\Doctrine\\Orm\\UserListener');
                $listenerService->addTag('doctrine.event_subscriber');
                break;

            case 'mongodb':
                $listenerService = new Definition('Rollerworks\\Bundle\\MultiUserBundle\\Doctrine\\MongoDB\\UserListener');
                $listenerService->addTag('doctrine_mongodb.odm.event_subscriber');
                break;

            case 'couchdb':
                $listenerService = new Definition('Rollerworks\\Bundle\\MultiUserBundle\\Doctrine\\CouchDB\\UserListener');
                $listenerService->addTag('doctrine_couchdb.event_subscriber');
                break;

            default:
                break;
        }

        if ($listenerService) {
            $listenerService->setArguments(array(
                new Reference('rollerworks_multi_user.service_container_injector'),
            ));

            $listenerService->setPublic(false);
            $container->setDefinition(sprintf('rollerworks_multi_user.%s.user_listener', $dbDriver), $listenerService);
        }
    }

    /**
     * Loads the primary services.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadServices(array $config, ContainerBuilder $container)
    {
        if ('fos_user.util.canonicalizer.default' === $config['service']['username_canonicalizer']) {
            $config['service']['username_canonicalizer'] = 'fos_user.util.username_canonicalizer';
        }

        if ('fos_user.util.canonicalizer.default' === $config['service']['email_canonicalizer']) {
            $config['service']['email_canonicalizer'] = 'fos_user.util.email_canonicalizer';
        }

        // Only create a UserManager service when not using a custom one
        if ('fos_user.user_manager.default' === $config['service']['user_manager']) {
            $config['service']['user_manager'] = $this->createUserManager($config, $container);
        }

        if (sprintf('%s.user_manager', $this->servicePrefix) !== $config['service']['user_manager']) {
            $container->setAlias(sprintf('%s.user_manager', $this->servicePrefix), $config['service']['user_manager']);
        }

        $container->setDefinition(sprintf('%s.user_provider.username', $this->servicePrefix), new DefinitionDecorator('fos_user.user_provider.username'))
        ->replaceArgument(0, new Reference(sprintf('%s.user_manager', $this->servicePrefix)))
        ->setPublic(false);

        $container->setDefinition(sprintf('%s.user_provider.username_email', $this->servicePrefix), new DefinitionDecorator('fos_user.user_provider.username_email'))
        ->replaceArgument(0, new Reference(sprintf('%s.user_manager', $this->servicePrefix)))
        ->setPublic(false);

        $container->setDefinition(sprintf('%s.user_manipulator', $this->servicePrefix), new DefinitionDecorator('fos_user.util.user_manipulator'))
        ->replaceArgument(0, new Reference(sprintf('%s.user_manager', $this->servicePrefix)));

        $container->setDefinition(sprintf('%s.listener.authentication', $this->servicePrefix), new DefinitionDecorator('fos_user.listener.authentication'))
        ->replaceArgument(1, sprintf('%%%s.firewall_name%%', $this->servicePrefix))
        ->addTag('kernel.event_subscriber');
    }

    private function createUserManager(array $config, ContainerBuilder $container)
    {
        $serviceName = sprintf('%s.user_manager.default', $this->servicePrefix);
        $modelManager = $this->loadModelManager($config, $container);

        $container->setDefinition($serviceName, new DefinitionDecorator('fos_user.user_manager.default'))
            ->replaceArgument(1, new Reference($config['service']['username_canonicalizer']))
            ->replaceArgument(2, new Reference($config['service']['email_canonicalizer']))
            ->replaceArgument(3, new Reference($modelManager))
            ->replaceArgument(4, sprintf('%%%s.model.user.class%%', $this->servicePrefix))
            ->setPublic(false);

        return $serviceName;
    }

    private function loadMailer(array $config, ContainerBuilder $container)
    {
        if ('fos_user.mailer.default' === $config['service']['mailer']) {
            $config['service']['mailer'] = sprintf('%s.mailer.default', $this->servicePrefix);

            $container->setDefinition($config['service']['mailer'], new DefinitionDecorator('fos_user.mailer.default'))
            ->replaceArgument(1, new Reference('rollerworks_multi_user.routing.user_discriminator_url_generator'))
            ->replaceArgument(3, array(
                'confirmation.template' => sprintf('%%%s.registration.confirmation.email.template%%', $this->servicePrefix),
                'resetting.template' => sprintf('%%%s.resetting.email.template%%', $this->servicePrefix),
                'from_email' => array(
                    'confirmation' => sprintf('%%%s.registration.confirmation.from_email%%', $this->servicePrefix),
                    'resetting' => sprintf('%%%s.resetting.email.from_email%%', $this->servicePrefix),
                )
            ))
            ->setPublic(false);
        }

        if ('fos_user.mailer.twig_swift' === $config['service']['mailer']) {
            $config['service']['mailer'] = sprintf('%s.mailer.twig_swift', $this->servicePrefix);

            $container->setDefinition($config['service']['mailer'], new DefinitionDecorator('fos_user.mailer.twig_swift'))
            ->replaceArgument(1, new Reference('rollerworks_multi_user.routing.user_discriminator_url_generator'))
            ->replaceArgument(3, array(
                'template' => array(
                    'confirmation' => sprintf('%%%s.registration.confirmation.email.template%%', $this->servicePrefix),
                    'resetting' => sprintf('%%%s.resetting.email.template%%', $this->servicePrefix)
                ),
                'from_email' => array(
                    'confirmation' => sprintf('%%%s.registration.confirmation.from_email%%', $this->servicePrefix),
                    'resetting' => sprintf('%%%s.resetting.email.from_email%%', $this->servicePrefix),
                )
            ))
            ->setPublic(false);
        }

        if (sprintf('%s.mailer', $this->servicePrefix) !== $config['service']['mailer']) {
            $container->setAlias(sprintf('%s.mailer', $this->servicePrefix), $config['service']['mailer']);
        }

        // Custom or noop-mailer
    }

    private function loadSecurity(array $config, ContainerBuilder $container, Definition $user)
    {
        $this->setTemplates('security.login', $config['login'], $user);
        $this->remapParametersNamespaces($config, $container, array(
            'login' => array('template' => $this->servicePrefix . '.security.login.template'),
        ));
    }

    private function loadProfile(array $config, ContainerBuilder $container, Definition $user)
    {
        $config['form'] = $this->convertFormType($container, 'profile', $config['form'], 'user');
        $this->createFormService($container, 'profile', $config, $user, 'user');
        $this->setTemplates('profile', $config, $user);

        $this->remapParametersNamespaces($config, $container, array(
            'form' => $this->servicePrefix . '.profile.form.%s',
            'template' => $this->servicePrefix . '.profile.%s.template',
        ));
    }

    private function loadRegistration(array $config, ContainerBuilder $container, Definition $user, array $fromEmail)
    {
        $config['form'] = $this->convertFormType($container, 'registration', $config['form'], 'user');
        $this->createFormService($container, 'registration', $config, $user, 'user');
        $this->setTemplates('registration', $config, $user);

        if (isset($config['confirmation']['from_email'])) {
            // overwrite the global one
            $fromEmail = $config['confirmation']['from_email'];
            unset($config['confirmation']['from_email']);
        }

        $container->setParameter($this->servicePrefix . '.registration.confirmation.from_email', array($fromEmail['address'] => $fromEmail['sender_name']));
        $user->addMethodCall('setConfig', array('registering.confirmation.enabled', isset($config['confirmation']) ? $config['confirmation']['enabled'] : false));

        $this->remapParametersNamespaces($config, $container, array(
            'confirmation' => $this->servicePrefix . '.registration.confirmation.%s',
            'form' => $this->servicePrefix . '.registration.form.%s',
            'template' => $this->servicePrefix . '.registration.%s.template',
        ));

        if (isset($config['confirmation'])) {
            $this->remapParametersNamespaces($config['confirmation'], $container, array(
                'template' => $this->servicePrefix . '.registration.confirmation.%s.template',
            ));

            $this->setTemplates('registration.confirmation', $config['confirmation'], $user);
        }
    }

    private function loadChangePassword(array $config, ContainerBuilder $container, Definition $user)
    {
        $config['form'] = $this->convertFormType($container, 'change_password', $config['form'], 'user');
        $this->createFormService($container, 'change_password', $config, $user, 'user');
        $this->setTemplates('change_password', $config, $user);

        $this->remapParametersNamespaces($config, $container, array(
            'form' => $this->servicePrefix . '.change_password.form.%s',
            'template' => $this->servicePrefix . '.change_password.%s.template',
        ));
    }

    private function loadResetting(array $config, ContainerBuilder $container, Definition $user, array $fromEmail)
    {
        $config['form'] = $this->convertFormType($container, 'resetting', $config['form'], 'user');
        $this->createFormService($container, 'resetting', $config, $user, 'user');
        $this->setTemplates('resetting', $config, $user);

        if (isset($config['email']['from_email'])) {
            // overwrite the global one
            $fromEmail = $config['email']['from_email'];
            unset($config['email']['from_email']);
        }

        $container->setParameter($this->servicePrefix . '.resetting.email.from_email', array($fromEmail['address'] => $fromEmail['sender_name']));
        $user->addMethodCall('setConfig', array('resetting.token_ttl', '%' . $this->servicePrefix . '.resetting.token_ttl' . '%'));

        $this->remapParametersNamespaces($config, $container, array(
            '' => array (
                'token_ttl' => $this->servicePrefix . '.resetting.token_ttl',
            ),
            'email' => $this->servicePrefix . '.resetting.email.%s',
            'form' => $this->servicePrefix . '.resetting.form.%s',
            'template' => $this->servicePrefix . '.resetting.%s.template',
        ));
    }

    private function loadGroups(array $config, ContainerBuilder $container, Definition $user)
    {
        $this->setTemplates('group', $config, $user);

        if ('fos_user.group_manager.default' === $config['group_manager']) {
            $config['group_manager'] = sprintf('%s.group_manager.default', $this->servicePrefix);
            $modelManager = $this->loadModelManager($config, $container);

            $container->setDefinition($config['group_manager'], new DefinitionDecorator('fos_user.group_manager.default'))
            ->replaceArgument(0, new Reference($modelManager))
            ->replaceArgument(1, sprintf('%%%s.model.group.class%%', $this->servicePrefix))
            ->setPublic(false);
        }

        $config['form'] = $this->convertFormType($container, 'group', $config['form'], 'group');
        $this->createFormService($container, 'group', $config, $user, 'group');

        if (sprintf('%s.group_manager', $this->servicePrefix) !== $config['group_manager']) {
            $container->setAlias(sprintf('%s.group_manager', $this->servicePrefix), $config['group_manager']);
        }

        $this->remapParametersNamespaces($config, $container, array(
            '' => array(
                'group_class' => $this->servicePrefix . '.model.group.class',
            ),
            'form' => $this->servicePrefix . '.group.form.%s',
            'template' => $this->servicePrefix . '.group.%s.template',
        ));
    }
}
