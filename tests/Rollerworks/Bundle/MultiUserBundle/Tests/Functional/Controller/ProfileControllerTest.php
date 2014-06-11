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
class ProfileControllerTest extends WebTestCaseFunctional
{
    /**
     * @runInSeparateProcess
     */
    public function testShowAdminProfile()
    {
        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'dummy-example', 'PHP_AUTH_PW' => 'mySecret0Password'));
        $user = $this->createUser('acme_admin', 'dummy-example', 'dummy-example@example.com', 'mySecret0Password');

        $crawler = $client->request('GET', '/admin/profile/');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("dummy-example")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("dummy-example@example.com")')->count());
    }

    /**
     * @runInSeparateProcess
     */
    public function testShowUserProfile()
    {
        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'user-example', 'PHP_AUTH_PW' => 'mySecret1Password'));
        $user = $this->createUser('acme_user', 'user-example', 'user-example@example.com', 'mySecret1Password');

        $crawler = $client->request('GET', '/user/profile/');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("user-example")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("user-example@example.com")')->count());
    }

    /**
     * @runInSeparateProcess
     */
    public function testEditAdminProfile()
    {
        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'dummy-example', 'PHP_AUTH_PW' => 'mySecret0Password'));
        $user = $this->createUser('acme_admin', 'dummy-example', 'dummy-example@example.com', 'mySecret0Password');

        $crawler = $client->request('GET', '/admin/profile/edit');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Update')->form();
        $form['acme_admin_profile_form[username]'] = 'dummy-example';
        $form['acme_admin_profile_form[email]'] = 'dummy-example@example.com';
        $form['acme_admin_profile_form[current_password]'] = 'mySecret0Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/profile/'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testEditUserProfile()
    {
        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'user-example', 'PHP_AUTH_PW' => 'mySecret1Password'));
        $user = $this->createUser('acme_user', 'user-example', 'user-example@example.com', 'mySecret1Password');

        $crawler = $client->request('GET', '/user/profile/edit');

        $this->assertEquals($crawler->filter('form')->count(), 1);

        $form = $crawler->selectButton('Update')->form();
        $form['acme_user_profile_form[username]'] = 'dummy-example';
        $form['acme_user_profile_form[email]'] = 'dummy-example@example.com';
        $form['acme_user_profile_form[current_password]'] = 'mySecret1Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/user/profile/'));
    }
}
