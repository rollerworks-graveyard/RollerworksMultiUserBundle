<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

error_reporting(E_ALL | E_STRICT);

// Disabled as fails with insulated tests, find a better way to do this
//if (!class_exists('PHPUnit_Framework_TestCase') || version_compare(PHPUnit_Runner_Version::id(), '3.5') < 0) {
//    throw new \RuntimeException('PHPUnit framework is required, at least 3.5 version');
//}
//
//if (!class_exists('PHPUnit_Framework_MockObject_MockBuilder')) {
//    throw new \RuntimeException('PHPUnit MockObject plugin is required, at least 1.0.8 version');
//}

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Did not find vendor/autoload.php. Please Install vendors using command: composer.phar install --dev');
}

if (version_compare(PHP_VERSION, '5.4', '>=') && gc_enabled()) {
    // Disabling Zend Garbage Collection to prevent segfaults with PHP5.4+
    // https://bugs.php.net/bug.php?id=53976
    gc_disable();
}

/**
 * @var $loader ClassLoader
 */
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Rollerworks\\Bundle\\MultiUserBundle\\Tests', __DIR__);

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// Do this explicit as the Swiftmailer is only loaded automatically when its used by the Application
// Without this ResettingControllerTest fails
require_once __DIR__ . '/../vendor/swiftmailer/swiftmailer/lib/classes/Swift.php';
Swift::registerAutoload();

// Don't use the system tempdir on Windows as that fails to work!
// The path gets to long when it also includes the 5 character hash of the Container with functional tests.
call_user_func(function () {
    if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
        $rootDir = substr(realpath(substr(__DIR__, 0, strpos(__DIR__, '\\')+1)), 0, -1);
    } else {
        $rootDir = sys_get_temp_dir();
    }

    $rootDir .= '/.tmp_c';
    putenv('TMPDIR=' . $rootDir);

    if (!is_dir($rootDir)) {
        mkdir($rootDir, 0777, true);
    }
});

return $loader;
