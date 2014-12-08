<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationConstraint;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Processor;

class ProcessedConfigurationEqualsConstraint extends AbstractConfigurationConstraint
{
    private $configurationValues;

    public function __construct(NodeDefinition $configuration, array $configurationValues)
    {
        $this->validateConfigurationValuesArray($configurationValues);
        $this->configurationValues = $configurationValues;
        $this->configuration = $configuration;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        $processedConfiguration = $this->processConfiguration($this->configurationValues);

        $constraint = new \PHPUnit_Framework_Constraint_IsEqual($other);

        return $constraint->evaluate($processedConfiguration, '', $returnResult);
    }

    public function toString()
    {
        // won't be used, this constraint only wraps \PHPUnit_Framework_Constraint_IsEqual
    }

    protected function processConfiguration(array $configurationValues)
    {
        $processor = new Processor();

        return $processor->process($this->configuration->end()->buildTree(), $configurationValues);
    }
}
