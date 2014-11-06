<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\DependencyInjection\Compiler;

use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass;
use Symfony\Component\DependencyInjection\Definition;

class RegisterFosUserMappingsPass extends RegisterMappingsPass
{
    public static function createOrmMappingDriver($servicePrefix)
    {
        $namespaces = self::getMappings();

        $arguments = array($namespaces, '.orm.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ORM\Mapping\Driver\XmlDriver', array($locator));

        $managerParameters = array($servicePrefix.'.model_manager_name', 'doctrine.default_entity_manager');
        $enabledParameter = $servicePrefix.'.backend_type_orm';

        return new RegisterFosUserMappingsPass(
            $driver,
            $namespaces,
            $managerParameters,
            'doctrine.orm.%s_metadata_driver',
            $enabledParameter,
            'doctrine.orm.%s_configuration'
        );
    }

    public static function createMongoDBMappingDriver($servicePrefix)
    {
        $namespaces = self::getMappings();

        $arguments = array($namespaces, '.mongodb.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver', array($locator));

        $managerParameters = array($servicePrefix.'.model_manager_name', 'doctrine_mongodb.odm.default_document_manager');
        $enabledParameter = $servicePrefix.'.backend_type_mongodb';

        return new RegisterFosUserMappingsPass(
            $driver,
            $namespaces,
            $managerParameters,
            'doctrine_mongodb.odm.%s_metadata_driver',
            $enabledParameter,
            'doctrine_mongodb.odm.%s_configuration'
        );
    }

    public static function createCouchDBMappingDriver($servicePrefix)
    {
        $namespaces = self::getMappings();

        $arguments = array($namespaces, '.couchdb.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\CouchDB\Mapping\Driver\XmlDriver', array($locator));

        $managerParameters = array($servicePrefix.'.model_manager_name', 'doctrine_couchdb.default_document_manager');
        $enabledParameter = $servicePrefix.'.backend_type_couchdb';

        return new RegisterFosUserMappingsPass(
            $driver,
            $namespaces,
            $managerParameters,
            'doctrine_couchdb.odm.%s_metadata_driver',
            $enabledParameter,
            'doctrine_couchdb.odm.%s_configuration'
        );
    }

    private static function getMappings()
    {
        static $mappings;

        if (null === $mappings) {
            $r = new \ReflectionClass('FOS\UserBundle\FOSUserBundle');
            $mappings = array(
                realpath(dirname($r->getFilename()).'/Resources/config/doctrine/model') => 'FOS\UserBundle\Model',
            );
        }

        return $mappings;
    }
}
