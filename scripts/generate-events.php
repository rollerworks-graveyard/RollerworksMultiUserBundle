<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Did not find vendor/autoload.php. Please Install vendors using command: composer.phar install --dev');
}

/**
 * @var $loader ClassLoader
 */
$loader = require_once __DIR__ . '/../vendor/autoload.php';

$eventsClass = new \ReflectionClass('FOS\UserBundle\FOSUserEvents');
$events = $eventsClass->getConstants();

$underscoreToCamelCase = function ($string) {
    $string = strtolower($string);

    return preg_replace_callback('/_([a-z])/', function ($c) {
        return strtoupper($c[1]);
    }, $string);
};

$date = date('Y-m-d');

echo <<<EOT

    // dispatchers and event-subscriber list is generated using scripts/generate-events.php
    // last updated on $date

EOT;

foreach ($events as $event => $eventName) {

    $event = ucfirst($underscoreToCamelCase($event));
    $eventName = substr($eventName, 8);

echo <<<EOT

    public function dispatch$event(Event \$e)
    {
        if (\$userSys = \$this->userDiscriminator->getCurrentUser()) {
            \$this->eventDispatcher->dispatch(\$userSys . '$eventName', \$e);
        }
    }

EOT;
}

echo <<<EOT

    /**
     * Subscribes to all events defined in FOS\UserBundle\FOSUserEvents.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(

EOT;

foreach ($events as $event => $eventName) {

    $eventFunc = ucfirst($underscoreToCamelCase($event));

echo <<<EOT
            FOSUserEvents::$event => 'dispatch$eventFunc',

EOT;
}

echo <<<EOT
        );
    }
EOT;
