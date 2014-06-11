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

use Rollerworks\Bundle\MultiUserBundle\Command\CreateUserCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class CreateUserCommandTest extends CommandTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testCreateUser()
    {
        $command = new CreateUserCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:create');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $this->assertNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'email' => 'test@example.com',
            'password' => 'very-not-secure',
            'user-system' => 'acme_admin'
        ));

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'email' => 'test@example.com',
            'password' => 'very-not-secure',
            'user-system' => 'acme_user'
        ));

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNotNull($acmeUserUserManager->findUserByUsername('testUser'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreateUserMissingUserSystem()
    {
        $command = new CreateUserCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:create');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $this->setExpectedException('LogicException', 'Impossible to set the user discriminator, because "" is not present in the users list.');

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'email' => 'test@example.com',
            'password' => 'very-not-secure'
        ));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreateUserInvalidUserSystem()
    {
        $command = new CreateUserCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:create');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $this->setExpectedException('LogicException', 'Impossible to set the user discriminator, because "acme_nothing" is not present in the users list.');

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'email' => 'test@example.com',
            'password' => 'very-not-secure',
            'user-system' => 'acme_nothing'
        ));
    }
}
