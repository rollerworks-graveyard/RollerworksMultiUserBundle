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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RegisterUserPass, registers the user-systems with the UserDiscriminator.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class RegisterUserPass implements CompilerPassInterface
{
    private $requestMatchers = array();

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('rollerworks_multi_user.user_discriminator')) {
            return;
        }

        $requestListener = null;
        $authenticationListener = null;

        if ($container->hasDefinition('rollerworks_multi_user.listener.request')) {
            $requestListener = $container->getDefinition('rollerworks_multi_user.listener.request');
        }

        if ($container->hasDefinition('rollerworks_multi_user.listener.authentication')) {
            $authenticationListener = $container->getDefinition('rollerworks_multi_user.listener.authentication');
        }

        $userDiscriminator = $container->getDefinition('rollerworks_multi_user.user_discriminator');

        foreach ($container->findTaggedServiceIds('rollerworks_multi_user.user_system') as $id => $attributes) {
            $name = $attributes[0]['alias'];

            if (!isset($attributes[0]['request_matcher'])) {
                $requestMatcher = $this->createRequestMatcher($container, $container->getParameterBag()->resolveValue($attributes[0]['path']), $container->getParameterBag()->resolveValue($attributes[0]['host']));
            } else {
                $requestMatcher = new Reference($attributes[0]['request_matcher']);
            }

            if ($authenticationListener) {
                $authenticationListener->addMethodCall('addUser', array($name, $container->getParameterBag()->resolveValue($attributes[0]['class'])));
            }

            if ($requestListener) {
                $requestListener->addMethodCall('addUser', array($name, $requestMatcher));
            }

            $userDiscriminator->addMethodCall('addUser', array($name, new Reference($id)));
        }
    }

    /**
     * Copied from the SymfonySecurityBundle.
     */
    private function createRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = array(), $ip = null, array $attributes = array())
    {
        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));
        $id = 'rollerworks_multi_user.request_matcher.'.md5($serialized).sha1($serialized);

        // Matcher already exist, which is not allowed
        if (isset($this->requestMatchers[$id])) {
            throw new \RuntimeException(sprintf(
                'There is already a request-matcher for this configuration: path: "%s", host: "%s". Only one request matcher should match for the user system.',
                $path,
                $host
            ));
        }

        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip, $attributes);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container
            ->register($id, '%rollerworks_multi_user.matcher.class%')
            ->setPublic(false)
            ->setArguments($arguments)
        ;

        return $this->requestMatchers[$id] = new Reference($id);
    }
}
