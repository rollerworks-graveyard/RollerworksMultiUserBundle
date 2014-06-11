<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Command;

use Rollerworks\Bundle\MultiUserBundle\Command\DeactivateUserCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class DeactivateUserCommandTest extends CommandTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testDeactivateUser()
    {
        $command = new DeactivateUserCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:deactivate');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $user = $acmeAdminUserManager->createUser();
        $user->setEmail('test@example.com');
        $user->setUsername('testUser');
        $user->setPlainPassword('very-not-secure');
        $user->setEnabled(true);

        $acmeAdminUserManager->updateUser($user);

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'user-system' => 'acme_admin'
        ));

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));
        $this->assertFalse($acmeAdminUserManager->findUserByUsername('testUser')->isEnabled());

    }
}
