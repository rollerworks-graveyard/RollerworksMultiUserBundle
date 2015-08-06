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

use Rollerworks\Bundle\MultiUserBundle\Command\PromoteUserCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class PromoteUserCommandCommandTest extends CommandTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testDemote()
    {
        $command = new PromoteUserCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:promote');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $user = $acmeAdminUserManager->createUser();
        $user->setEmail('test@example.com');
        $user->setUsername('testUser');
        $user->setPlainPassword('very-not-secure');
        $user->setEnabled(false);

        $acmeAdminUserManager->updateUser($user);

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));
        $this->assertNotContains('ROLE_INVOICE_CREATE', $acmeAdminUserManager->findUserByUsername('testUser')->getRoles());

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'role' => 'ROLE_INVOICE_CREATE',
            '--user-system' => 'acme_admin',
        ));

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));

        $this->assertContains('ROLE_INVOICE_CREATE', $acmeAdminUserManager->findUserByUsername('testUser')->getRoles());
    }
}
