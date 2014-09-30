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

use Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessedValueContainsAreAllEmptyByDefault()
    {
        $this->assertProcessedConfigurationEquals(array(), array());
    }

    public function testProcessedValuesRespectOriginalValues()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'path' => '/',
                    'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',

                    'profile' => false,
                    'registration' => false,
                    'resetting' => false,
                    'change_password' => false,
                ),
                array(
                    'path' => '/user',
                ),
            ),
            array(
                'path' => '/user',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
            )
        );
    }

    public function testConfigurationAllSectionsEnabledByDefault()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'path' => '/',
                    'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                    'profile' => array(),
                    'change_password' => array(),
                    'registration' => array(),
                    'resetting' => array(),
                    'group' => array(),
                    'security' => array(),
                    'template' => array(),
                ),
            ),
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'profile' => array(),
                'change_password' => array(),
                'registration' => array(),
                'resetting' => array(),
                'group' => array(),
                'security' => array(),
                'template' => array(),
            )
        );
    }

    public function testConfigurationBCLayer()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'path' => '/',
                    'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                    'profile' => array(),
                    'change_password' => array(),
                    'registration' => array(),
                    'security' => array(),
                    'template' => array(),
                ),
            ),
            array(
                'path' => '/',
                'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                'profile' => array(),
                'change_password' => array(),
                'registration' => array(),
                'security' => array(),
                'template' => array(),
            ),
            array('profile', 'change_password', 'registration')
        );

        $this->assertConfigurationIsInvalid(
            array(
                array(
                    'path' => '/',
                    'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',
                    'profile' => array(),
                    'change_password' => array(),
                    'registration' => array(),
                    'security' => array(),
                    'template' => array(),
                    'resetting' => array(),
                ),
            ),
            array('profile', 'change_password', 'registration'),
            'Unrecognized options "resetting" under "user"'
        );
    }

    public function testConfigurationRequiresSectionEnabled()
    {
        $this->assertConfigurationIsInvalid(
            array(
                array(
                    'path' => '/',
                    'user_class' => 'Rollerworks\Bundle\MultiUserBundle\Tests\Stub\User',

                    'profile' => array(),
                ),
                array(
                    'path' => '/user',
                ),
            ),
            Configuration::CONFIG_ALL ^ Configuration::CONFIG_SECTION_PROFILE,
            'Unrecognized options "profile" under "user"'
        );
    }

    /**
     * @dataProvider getConfigs
     */
    public function testConfigurationSectionGetsEnabled($section, $config, $message = null, $enableServices = null)
    {
        if (null === $enableServices) {
            $enableServices = constant('Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Configuration::CONFIG_SECTION_'.strtoupper($section));
            $message = 'Unrecognized options "'.$section.'" under "user"';
        }

        $this->assertProcessedConfigurationEquals(
            array(
                $config,
            ),
            $config,
            $enableServices
        );

        $this->assertConfigurationIsInvalid(
            array(
                $config,
            ),
            Configuration::CONFIG_ALL ^ $enableServices,
            $message
        );
    }

    public static function getConfigs()
    {
        return array(
            array('profile', array('profile' => array())),
            array('change_password', array('change_password' => array())),
            array('registration', array('registration' => array())),
            array('resetting', array('resetting' => array())),
            array('group', array('group' => array())),

            array('db_driver', array('db_driver' => 'orm'), 'Unrecognized options "db_driver" under "user"', Configuration::CONFIG_DB_DRIVER),
            array('request_matcher', array('request_matcher' => ''), 'Unrecognized options "request_matcher" under "user"', Configuration::CONFIG_REQUEST_MATCHER),
            array('path', array('path' => ''), 'Unrecognized options "path" under "user"', Configuration::CONFIG_REQUEST_MATCHER),
            array('host', array('host' => ''), 'Unrecognized options "host" under "user"', Configuration::CONFIG_REQUEST_MATCHER),
            array('user_class', array('user_class' => 'stdClass'), 'Unrecognized options "user_class" under "user"', Configuration::CONFIG_USER_CLASS),
        );
    }

    /**
     * @param integer|array $enableServices
     *
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getTreeBuilderRoot($enableServices = Configuration::CONFIG_ALL)
    {
        $configuration = new Configuration();

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('user');
        $configuration->addUserConfig($rootNode, $enableServices);

        return $rootNode;
    }

    /**
     * Assert that the given configuration values are invalid.
     *
     * Optionally provide (part of) the exception message that you expect to receive.
     *
     * @param array       $configurationValues
     * @param integer     $enableServices
     * @param string|null $expectedMessage
     */
    protected function assertConfigurationIsInvalid(array $configurationValues, $enableServices = Configuration::CONFIG_ALL, $expectedMessage = null)
    {
        self::assertThat(
            $configurationValues,
            new ConfigurationValuesAreInvalidConstraint(
                $this->getTreeBuilderRoot($enableServices),
                $expectedMessage
            )
        );
    }

    /**
     * Assert that the given configuration values, when processed, will equal to the given array
     *
     * @param array   $configurationValues
     * @param array   $expectedProcessedConfiguration
     * @param integer $enableServices
     */
    protected function assertProcessedConfigurationEquals(array $configurationValues, array $expectedProcessedConfiguration, $enableServices = Configuration::CONFIG_ALL)
    {
        self::assertThat(
            $expectedProcessedConfiguration,
            new ProcessedConfigurationEqualsConstraint(
                $this->getTreeBuilderRoot($enableServices),
                $configurationValues
            )
        );
    }
}
