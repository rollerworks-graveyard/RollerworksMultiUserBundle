<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\AdminBundle\DependencyInjection;

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration as UserConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acme_admin');

        $rootNode
            ->children()
                ->booleanNode('remove_registration')->defaultFalse()->end()
            ->end();

        $configuration = new UserConfiguration();
        $configuration->addUserConfig($rootNode);

        return $treeBuilder;
    }
}
