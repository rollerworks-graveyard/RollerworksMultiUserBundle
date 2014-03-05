<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class WebTestCaseFunctional extends WebTestCase
{
    private static $dbIsSetUp = false;

    protected static function createKernel(array $options = array())
    {
        return new AppKernel(
            isset($options['config']) ? $options['config'] : 'default.yml'
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->remove(getenv('TMPDIR') . '/MultiUserBundle');
    }

    protected function tearDown()
    {
        parent::tearDown();

        self::$dbIsSetUp = false;
    }

    /**
     * @param array $options
     * @param array $server
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected static function newClient(array $options = array(), array $server = array())
    {
        $client = static::createClient(array_merge(array('config' => 'default.yml'), $options), $server);

        if (false === self::$dbIsSetUp) {
            $em = $client->getContainer()->get('doctrine.orm.default_entity_manager');

            // Initialize the database
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->updateSchema($em->getMetadataFactory()->getAllMetadata(), false);

            self::$dbIsSetUp = true;
        }

        return $client;
    }
}
