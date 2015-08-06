<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Command;

use Rollerworks\Bundle\MultiUserBundle\Command\ChangePasswordCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class ChangePasswordCommandTest extends CommandTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testChangePassword()
    {
        $command = new ChangePasswordCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:change-password');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $user = $acmeAdminUserManager->createUser();
        $user->setEmail('test@example.com');
        $user->setUsername('testUser');
        $user->setPlainPassword('very-not-secure');
        $user->setSalt('pepper');

        $acmeAdminUserManager->updateUser($user);

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'password' => 'very-not-secure-or-something-like-that',
            '--user-system' => 'acme_admin',
        ));

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));
        $this->assertEquals('9yAbiKi2nm0TBdTh4YY1QJSeZ0UAVppiz/UyxZRfsghWhvoR36k44KOBn9x3stCNSW0vvJyvq/IaVNvvx5P7Ug==', $acmeAdminUserManager->findUserByUsername('testUser')->getPassword());
    }
}
