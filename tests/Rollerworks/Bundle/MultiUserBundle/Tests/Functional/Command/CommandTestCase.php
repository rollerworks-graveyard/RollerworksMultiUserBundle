<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Command;

use Rollerworks\Bundle\MultiUserBundle\Tests\Functional\WebTestCaseFunctional;
use Symfony\Bundle\FrameworkBundle\Console\Application;

abstract class CommandTestCase extends WebTestCaseFunctional
{
    /**
     * @var Application
     */
    protected $application;

    public function setUp()
    {
        $client = static::newClient(array('config' => 'admin.yml'));
        $this->application = new Application($client->getKernel());

        $this->deleteAllUsers('acme_admin');
        $this->deleteAllUsers('acme_user');
    }

    protected function deleteAllUsers($userSys)
    {
        $container = $this->application->getKernel()->getContainer();
        $userManager = $container->get($userSys . '.user_manager');

        foreach ($userManager->findUsers() as $user) {
            $userManager->deleteUser($user);
        }
    }
}
