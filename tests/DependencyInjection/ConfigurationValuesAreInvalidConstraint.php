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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationValuesAreInvalidConstraint extends AbstractConfigurationConstraint
{
    private $expectedMessage;

    public function __construct(NodeDefinition $configuration, $expectedMessage = null)
    {
        $this->expectedMessage = $expectedMessage;
        $this->configuration = $configuration;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        $this->validateConfigurationValuesArray($other);

        try {
            $this->processConfiguration($other);
        } catch (InvalidConfigurationException $exception) {
            return $this->evaluateException($exception, $description, $returnResult);
        }

        if ($returnResult) {
            return false;
        }

        $this->fail($other, $description);
    }

    public function toString()
    {
        $toString = 'is invalid for the given configuration';

        if ($this->expectedMessage !== null) {
            $toString .= ' (expected exception message: '.$this->expectedMessage.')';
        }

        return $toString;
    }

    private function evaluateException(\Exception $exception, $description, $returnResult)
    {
        if ($this->expectedMessage === null) {
            return true;
        }

        // reuse the exception message constraint from PHPUnit itself
        $constraint = new \PHPUnit_Framework_Constraint_ExceptionMessage($this->expectedMessage);

        return $constraint->evaluate($exception, $description, $returnResult);
    }

    protected function processConfiguration(array $configurationValues)
    {
        $processor = new Processor();

        return $processor->process($this->configuration->end()->buildTree(), $configurationValues);
    }
}
