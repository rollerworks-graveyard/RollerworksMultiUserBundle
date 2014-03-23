<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
