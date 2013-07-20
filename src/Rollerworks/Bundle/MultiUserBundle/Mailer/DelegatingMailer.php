<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Mailer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

class DelegatingMailer implements MailerInterface
{
    private $userDiscriminator;
    private $container;

    public function __construct(UserDiscriminatorInterface $userDiscriminator, ContainerInterface $container)
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->container = $container;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $this->container->get($this->userDiscriminator->getCurrentUserConfig()->getServicePrefix() . '.mailer')->sendConfirmation($user);
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $this->container->get($this->userDiscriminator->getCurrentUserConfig()->getServicePrefix() . '.mailer')->sendResettingEmailMessage($user);
    }
}
