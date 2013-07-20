<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Stub;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;

class CustomMailer implements MailerInterface
{
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        // noop
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        // noop
    }
}
