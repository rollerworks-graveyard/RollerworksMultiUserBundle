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
class RegistrationControllerTest extends WebTestCaseFunctional
{
    /**
     * @runInSeparateProcess
     */
    public function testRegisterAdmin()
    {
        $client = self::newClient(array('config' => 'admin.yml'));
        $crawler = $client->request('GET', '/admin/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['fos_user_registration_form[username]'] = 'dummy-example';
        $form['fos_user_registration_form[email]'] = 'dummy-example@example.com';
        $form['fos_user_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['fos_user_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/register/confirmed'));

        // Now make the user is registered with the proper user-manager
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
        $crawler = $client->request('GET', '/user/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['acme_user_registration_form[username]'] = 'dummy-example';
        $form['acme_user_registration_form[email]'] = 'dummy-example@example.com';
        $form['acme_user_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_user_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/user/register/confirmed'));

        // Now make the user is registered with the proper user-manager
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
        $crawler = $client->request('GET', '/user/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['acme_user_registration_form[username]'] = 'dummy-example';
        $form['acme_user_registration_form[email]'] = 'dummy-example@example.com';
        $form['acme_user_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['acme_user_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/user/register/confirmed'));

        $client = self::newClient(array('config' => 'admin.yml'));
        $crawler = $client->request('GET', '/admin/register/');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Register')->form();
        $form['fos_user_registration_form[username]'] = 'dummy-example';
        $form['fos_user_registration_form[email]'] = 'dummy-example2@example.com';
        $form['fos_user_registration_form[plainPassword][first]'] = 'mySecret0Password';
        $form['fos_user_registration_form[plainPassword][second]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/register/confirmed'));

        // Now make the user is registered with the proper user-manager
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
