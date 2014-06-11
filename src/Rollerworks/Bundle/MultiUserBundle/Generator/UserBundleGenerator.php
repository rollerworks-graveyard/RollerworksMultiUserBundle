<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generates a bundle.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Fabien Potencier
 */
class UserBundleGenerator extends Generator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $namespace
     * @param string $bundle
     * @param string $dir
     * @param string $format
     * @param string $structure
     * @param string $dbDriver
     *
     * @throws \RuntimeException
     */
    public function generate($namespace, $bundle, $dir, $format, $structure, $dbDriver)
    {
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $driverDir = array(
            'orm' => 'Entity',
            'mongodb' => 'Document',
            'couchdb' => 'CouchDocument',
            'custom' => 'Model',
        );

        $basename = substr($bundle, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $bundle,
            'format'    => $format,
            'db_driver'    => $dbDriver,
            'model_namespace' => $namespace . '\\' . $driverDir[$dbDriver],
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );

        $this->renderFile('bundle/Bundle.php.twig', $dir.'/'.$bundle.'.php', $parameters);
        $this->renderFile('bundle/Extension.php.twig', $dir.'/DependencyInjection/'.$basename.'Extension.php', $parameters);
        $this->renderFile('bundle/Configuration.php.twig', $dir.'/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile('bundle/DefaultController.php.twig', $dir.'/Controller/DefaultController.php', $parameters);
        $this->renderFile('bundle/DefaultControllerTest.php.twig', $dir.'/Tests/Controller/DefaultControllerTest.php', $parameters);
        $this->renderFile('bundle/index.html.twig.twig', $dir.'/Resources/views/Default/index.html.twig', $parameters);
        $this->renderFile('bundle/services.'.$format.'.twig', $dir.'/Resources/config/services.'.$format, $parameters);

        $this->generateRoutes($namespace, $bundle, $dir, $format, $basename);
        $this->generateModels($namespace, $bundle, $dir, $dbDriver, $basename);
        $this->generateEventsClass($namespace, $bundle, $dir, $dbDriver, $basename);

        if ($structure) {
            $this->renderFile('bundle/messages.fr.xlf', $dir.'/Resources/translations/messages.fr.xlf', $parameters);

            $this->filesystem->mkdir($dir.'/Resources/doc');
            $this->filesystem->touch($dir.'/Resources/doc/index.rst');
            $this->filesystem->mkdir($dir.'/Resources/translations');
            $this->filesystem->mkdir($dir.'/Resources/public/css');
            $this->filesystem->mkdir($dir.'/Resources/public/images');
            $this->filesystem->mkdir($dir.'/Resources/public/js');
        }
    }

    /**
     * @param string $namespace
     * @param string $bundle
     * @param string $dir
     * @param string $dbDriver
     * @param string $basename
     */
    private function generateModels($namespace, $bundle, $dir, $dbDriver, $basename)
    {
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $bundle,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );

        $driverDir = array(
            'orm' => 'Entity',
            'mongodb' => 'Document',
            'couchdb' => 'CouchDocument',
            'custom' => 'Model',
        );

        $dir = $dir.'/'.$driverDir[$dbDriver];

        $this->renderFile('bundle/model/user_'.$dbDriver.'.php.twig', $dir.'/User.php', $parameters);
        $this->renderFile('bundle/model/group_'.$dbDriver.'.php.twig', $dir.'/Group.php', $parameters);
    }

    /**
     * @param string $namespace
     * @param string $bundle
     * @param string $dir
     * @param string $format
     * @param string $basename
     */
    private function generateRoutes($namespace, $bundle, $dir, $format, $basename)
    {
        $baseRoutes = array(
            'change_password' => array(
                'change_password' => array(
                    'path' => '/change-password',
                    'method' => array('GET', 'POST'),
                    'controller_action' => 'FOSUserBundle:ChangePassword:changePassword',
                ),
            ),

            'group' => array(
                'group_list' => array(
                    'path' => '/list',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Group:list',
                ),
                'group_new' => array(
                    'path' => '/new',
                    'method' => array(),
                    'controller_action' => 'FOSUserBundle:Group:new',
                ),
                'group_show' => array(
                    'path' => '/{groupName}',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Group:show',
                ),
                'group_edit' => array(
                    'path' => '/{groupName}/edit',
                    'method' => array(),
                    'controller_action' => 'FOSUserBundle:Group:edit',
                ),
                'group_delete' => array(
                    'path' => '/{groupName}/delete',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Group:delete',
                ),
            ),

            'profile' => array(
                'profile_show' => array(
                    'path' => '/',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Profile:show',
                ),
                'profile_edit' => array(
                    'path' => '/edit',
                    'method' => array(),
                    'controller_action' => 'FOSUserBundle:Profile:edit',
                ),
            ),

            'registration' => array(
                'registration_register' => array(
                    'path' => '/',
                    'method' => array(),
                    'controller_action' => 'FOSUserBundle:Registration:register',
                ),
                'registration_check_email' => array(
                    'path' => '/check-email',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Registration:checkEmail',
                ),
                'registration_confirm' => array(
                    'path' => '/confirm/{token}',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Registration:confirm',
                ),
                'registration_confirmed' => array(
                    'path' => '/confirmed',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Registration:confirmed',
                ),
            ),

            'resetting' => array(
                'resetting_request' => array(
                    'path' => '/request',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Resetting:request',
                ),
                'resetting_send_email' => array(
                    'path' => '/send-email',
                    'method' => array('POST'),
                    'controller_action' => 'FOSUserBundle:Resetting:sendEmail',
                ),
                'resetting_check_email' => array(
                    'path' => '/check-email',
                    'method' => array('GET'),
                    'controller_action' => 'FOSUserBundle:Resetting:checkEmail',
                ),
                'resetting_reset' => array(
                    'path' => '/reset/{token}',
                    'method' => array('GET', 'POST'),
                    'controller_action' => 'FOSUserBundle:Resetting:reset',
                ),
            ),

            'security' => array(
                'security_login' => array(
                    'path' => '/login',
                    'method' => array(),
                    'controller_action' => 'FOSUserBundle:Security:login',
                ),
                'security_check' => array(
                    'path' => '/login_check',
                    'method' => array('POST'),
                    'controller_action' => 'FOSUserBundle:Security:check',
                ),
                'security_logout' => array(
                    'path' => '/logout',
                    'method' => array(),
                    'controller_action' => 'FOSUserBundle:Security:logout',
                ),
            ),
        );

        foreach ($baseRoutes as $fileName => $routes) {
            $parameters = array(
                'namespace' => $namespace,
                'bundle'    => $bundle,
                'format'    => $format,
                'bundle_basename' => $basename,
                'extension_alias' => Container::underscore($basename),
                'routes' => $routes
            );

            $this->renderFile('bundle/routing.'.$format.'.twig', $dir.'/Resources/config/routing/'.$fileName.'.'.$format, $parameters);
        }
    }

    private function generateEventsClass($namespace, $bundle, $dir, $format, $basename)
    {
        $eventsClass = new \ReflectionClass('FOS\UserBundle\FOSUserEvents');
        $events = $eventsClass->getConstants();

        foreach ($events as $name => $event) {
            $events[$name] = Container::underscore($basename).substr($event, 8);
        }

        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $bundle,
            'format'    => $format,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
            'events' => $events
        );

        $this->renderFile('bundle/Events.php.twig', $dir.'/'.$basename.'Events.php', $parameters);
    }
}
