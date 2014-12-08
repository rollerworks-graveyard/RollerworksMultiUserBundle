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
class ChangePasswordControllerTest extends WebTestCaseFunctional
{
    /**
     * @runInSeparateProcess
     */
    public function testChangePasswordAdminProfile()
    {
        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'dummy-example', 'PHP_AUTH_PW' => 'mySecret0Password'));
        $user = $this->createUser('acme_admin', 'dummy-example', 'dummy-example@example.com', 'mySecret0Password');

        $crawler = $client->request('GET', '/admin/profile/change-password');

        $this->assertEquals($crawler->filter('form')->count(), 1);
        $form = $crawler->selectButton('Change password')->form();
        $form['acme_admin_change_password_form[current_password]'] = 'mySecret0Password';
        $form['acme_admin_change_password_form[plainPassword][first]'] = 'mySecret12Password';
        $form['acme_admin_change_password_form[plainPassword][second]'] = 'mySecret12Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/admin/profile/'));

        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'dummy-example', 'PHP_AUTH_PW' => 'mySecret12Password'));
        $crawler = $client->request('GET', '/admin/profile/');
        $this->assertNotEquals(401, $client->getResponse()->getStatusCode());

        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'dummy-example', 'PHP_AUTH_PW' => 'mySecret0Password'));
        $crawler = $client->request('GET', '/admin/profile/');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @runInSeparateProcess
     */
    public function testChangePasswordUserProfile()
    {
        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'user-example', 'PHP_AUTH_PW' => 'mySecret1Password'));
        $user = $this->createUser('acme_user', 'user-example', 'user-example@example.com', 'mySecret1Password');

        $crawler = $client->request('GET', '/user/profile/change-password');

        $this->assertEquals($crawler->filter('form')->count(), 1);
        $form = $crawler->selectButton('Change password')->form();
        $form['acme_user_change_password_form[current_password]'] = 'mySecret1Password';
        $form['acme_user_change_password_form[plainPassword][first]'] = 'mySecret21Password';
        $form['acme_user_change_password_form[plainPassword][second]'] = 'mySecret21Password';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/user/profile/'));

        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'user-example', 'PHP_AUTH_PW' => 'mySecret21Password'));
        $crawler = $client->request('GET', '/user/profile/');
        $this->assertNotEquals(401, $client->getResponse()->getStatusCode());

        $client = self::newClient(array('config' => 'admin.yml'), array('PHP_AUTH_USER' => 'user-example', 'PHP_AUTH_PW' => 'mySecret1Password'));
        $crawler = $client->request('GET', '/user/profile/');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
