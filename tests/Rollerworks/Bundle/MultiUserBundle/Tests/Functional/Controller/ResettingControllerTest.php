<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Controller;

use Rollerworks\Bundle\MultiUserBundle\Tests\Functional\WebTestCaseFunctional;

/**
 * @group functional
 */
class ResettingControllerTest extends WebTestCaseFunctional
{
    /**
     * @runInSeparateProcess
     */
    public function testResettingAdmin()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $client->insulate(); // runInSeparateProcess fails with an fatal error so we use this instead.
        $client->enableProfiler();

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

        $crawler = $client->request('GET', '/admin/resetting/request');
        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Reset password')->form();
        $form['username'] = 'dummy-example';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/resetting/check-email?email=...%40example.com'));

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages('default');
        $message = $collectedMessages[0];

        // Asserting that the correct URL is used
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertRegExp('{To reset your password - please visit http://[^/]+/admin/resetting/reset/[^\s]+}i', $message->getBody());

        if (preg_match('{To reset your password - please visit http://[^/]+/admin/resetting/reset/([^\s]+)}i', $message->getBody(), $match) < 0) {
            $this->fail('Regex did not match email message: ' . $message->getBody());
        }

        $token = $match[1];

        $crawler = $client->request('GET', '/admin/resetting/reset/' . $token);
        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Change password')->form();
        $form['acme_admin_resetting_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_admin_resetting_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/profile/'));
    }

    /**
     * @runInSeparateProcess
     */
//    public function testResettingAdminWithNoRegistration()
//    {
//        $client = self::newClient(array('config' => 'admin_no_reg.yml'));
//        $client->insulate(); // runInSeparateProcess fails with an fatal error so we use this instead.
//        $client->enableProfiler();
//
//        $client->getContainer()->get('rollerworks_multi_user.user_discriminator')->setCurrentUser('acme_admin');
//
//        /** @var \FOS\UserBundle\Model\UserManagerInterface $userManager */
//        $userManager = $client->getContainer()->get('acme_admin.user_manager');
//
//        $admin = $userManager->createUser();
//        $admin
//            ->setUsername('dummy-example')
//            ->setEmail('dummy-example@example.com')
//            ->setPlainPassword('mySecret0Password')
//        ;
//
//        $userManager->updateUser($admin);
//
//        $crawler = $client->request('GET', '/admin/resetting/request');
//        $this->assertEquals($crawler->filter('form')->count(), 1);
//
//        $form = $crawler->selectButton('Reset password')->form();
//        $form['username'] = 'dummy-example';
//
//        $client->submit($form);
//        $this->assertTrue($client->getResponse()->isRedirect('/admin/resetting/check-email?email=...%40example.com'));
//
//        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
//
//        // Check that an e-mail was sent
//        $this->assertEquals(1, $mailCollector->getMessageCount());
//
//        $collectedMessages = $mailCollector->getMessages('default');
//        $message = $collectedMessages[0];
//
//        // Asserting that the correct URL is used
//        $this->assertInstanceOf('Swift_Message', $message);
//        $this->assertRegExp('{To reset your password - please visit http://[^/]+/admin/resetting/reset/[^\s]+}i', $message->getBody());
//
//        if (preg_match('{To reset your password - please visit http://[^/]+/admin/resetting/reset/([^\s]+)}i', $message->getBody(), $match) < 0) {
//            $this->fail('Regex did not match email message: ' . $message->getBody());
//        }
//
//        $token = $match[1];
//
//        $crawler = $client->request('GET', '/admin/resetting/reset/' . $token);
//        $this->assertEquals($crawler->filter('form')->count(), 1);
//
//        $form = $crawler->selectButton('Change password')->form();
//        $form['acme_admin_resetting_form[plainPassword][first]'] = 'mySecret0Password';
//        $form['acme_admin_resetting_form[plainPassword][second]'] = 'mySecret0Password';
//
//        $client->submit($form);
//        $this->assertTrue($client->getResponse()->isRedirect('/admin/profile/'));
//    }
}
