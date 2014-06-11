<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Controller;

use Rollerworks\Bundle\MultiUserBundle\Tests\Functional\WebTestCaseFunctional;

/**
 * @group functional
 */
class SecurityControllerTest extends WebTestCaseFunctional
{
    /**
     * @runInSeparateProcess
     */
    public function testLoginAdmin()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $client->getContainer()->get('rollerworks_multi_user.user_discriminator')->setCurrentUser('acme_admin');

        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $client->getContainer()->get('acme_admin.user_manager');

        $admin = $userManager->createUser();
        $admin
            ->setUsername('dummy-example')
            ->setEmail('dummy-example@example.com')
            ->setPlainPassword('mySecret0Password')
        ;

        $userManager->updateUser($admin);

        $crawler = $client->request('GET', '/admin/login');
        $this->assertEquals($crawler->filter('#admin-login-form')->count(), 1);
    }

    /**
     * @runInSeparateProcess
     */
    public function testLoginUser()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $client->getContainer()->get('rollerworks_multi_user.user_discriminator')->setCurrentUser('acme_user');

        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
        $userManager = $client->getContainer()->get('acme_user.user_manager');

        $user = $userManager->createUser();
        $user
            ->setUsername('dummy-example')
            ->setEmail('dummy-example@example.com')
            ->setPlainPassword('mySecret0Password')
        ;

        $userManager->updateUser($user);

        $crawler = $client->request('GET', '/user/login');
        $this->assertEquals($crawler->filter('#user-login-form')->count(), 1);
    }
}
