<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Mailer;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Rollerworks\Component\SfContainerInjector\ContainerInjector;

class DelegatingMailer implements MailerInterface
{
    private $userDiscriminator;
    private $container;

    public function __construct(UserDiscriminatorInterface $userDiscriminator, ContainerInjector $container)
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->container = $container;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $this->container->get($this->userDiscriminator->getCurrentUserConfig()->getServicePrefix().'.mailer')->sendConfirmationEmailMessage($user);
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $this->container->get($this->userDiscriminator->getCurrentUserConfig()->getServicePrefix().'.mailer')->sendResettingEmailMessage($user);
    }
}
