<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
