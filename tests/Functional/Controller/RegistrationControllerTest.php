<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Controller;

use Rollerworks\Bundle\MultiUserBundle\Tests\Functional\WebTestCaseFunctional;

/**
 * @group functional
 */
class RegistrationControllerTest extends WebTestCaseFunctional
{
    /**
     * @runInSeparateProcess
     */
    public function testRegisterAdmin()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $client->insulate();
        $client->enableProfiler();

        $crawler = $client->request('GET', '/admin/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['acme_admin_registration_form[username]'] = 'dummy-example';
        $form['acme_admin_registration_form[email]'] = 'dummy-example@example.com';
        $form['acme_admin_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_admin_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/register/confirmed'));

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was NOT sent
        $this->assertEquals(0, $mailCollector->getMessageCount());

        // Manual discrimination because the profiler breaks our request
        $client->getContainer()->get('rollerworks_multi_user.user_discriminator')->setCurrentUser('acme_admin');

        // Now make sure the user is registered with the proper user-manager
        $this->assertNotNull($client->getContainer()->get('acme_admin.user_manager')->findUserByUsername('dummy-example'));
        $this->assertNull($client->getContainer()->get('acme_user.user_manager')->findUserByUsername('dummy-example'));
        $this->assertNotNull($client->getContainer()->get('fos_user.user_manager')->findUserByUsername('dummy-example'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegisterUser()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $client->insulate();
        $client->enableProfiler();

        $crawler = $client->request('GET', '/user/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['acme_user_registration_form[username]'] = 'dummy-example';
        $form['acme_user_registration_form[email]'] = 'dummy-example@example.com';
        $form['acme_user_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_user_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/user/register/check-email'));

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages('default');
        $message = $collectedMessages[0];

        // Asserting that the correct URL is used
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertRegExp('{Hello dummy-example!}i', $message->getBody());
        $this->assertRegExp('{To finish activating your account - please visit http://[^/]+/user/register/confirm/[^\s]+}i', $message->getBody());

        // Manual discrimination because the profiler breaks our request
        $client->getContainer()->get('rollerworks_multi_user.user_discriminator')->setCurrentUser('acme_user');

        // Now make sure the user is registered with the proper user-manager
        $this->assertNotNull($client->getContainer()->get('acme_user.user_manager')->findUserByUsername('dummy-example'));
        $this->assertNull($client->getContainer()->get('acme_admin.user_manager')->findUserByUsername('dummy-example'));
        $this->assertNotNull($client->getContainer()->get('fos_user.user_manager')->findUserByUsername('dummy-example'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegisterUserAndAdmin()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $client->insulate();
        $client->enableProfiler();

        $crawler = $client->request('GET', '/user/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['acme_user_registration_form[username]'] = 'dummy-example';
        $form['acme_user_registration_form[email]'] = 'dummy-example@example.com';
        $form['acme_user_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_user_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/user/register/check-email'));

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages('default');
        $message = $collectedMessages[0];

        // Asserting that the correct URL is used
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertRegExp('{Hello dummy-example!}i', $message->getBody());
        $this->assertRegExp('{To finish activating your account - please visit http://[^/]+/user/register/confirm/[^\s]+}i', $message->getBody());

        $client = self::newClient(array('config' => 'admin.yml'));
        $crawler = $client->request('GET', '/admin/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['acme_admin_registration_form[username]'] = 'dummy-example';
        $form['acme_admin_registration_form[email]'] = 'dummy-example2@example.com';
        $form['acme_admin_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_admin_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/register/confirmed'));

        // Now make sure the user is registered with the proper user-manager
        $user = $client->getContainer()->get('acme_user.user_manager')->findUserByUsername('dummy-example');
        $admin = $client->getContainer()->get('acme_admin.user_manager')->findUserByUsername('dummy-example');

        $this->assertNotNull($user);
        $this->assertNotNull($admin);
        $this->assertNotSame($admin, $user);

        // Because admin was used as last the current user-system is admin
        $current = $client->getContainer()->get('fos_user.user_manager')->findUserByUsername('dummy-example');

        $this->assertNotNull($current);
        $this->assertSame($admin, $current);

        // And now switch, don't use same assertion as the kernel is rebooted
        $client->request('GET', '/user/register/');

        // Because admin was used as last the current user-system is admin
        $current = $client->getContainer()->get('fos_user.user_manager')->findUserByUsername('dummy-example');

        $this->assertNotNull($current);
        $this->assertEquals('dummy-example@example.com', $current->getEmail());
    }
}
