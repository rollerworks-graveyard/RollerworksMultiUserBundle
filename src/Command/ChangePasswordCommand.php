<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Command;

use FOS\UserBundle\Command\ChangePasswordCommand as BaseChangePasswordCommand;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class ChangePasswordCommand extends BaseChangePasswordCommand
{
    protected function configure()
    {
        parent::configure();

        $definition = $this->getDefinition();
        $definition->addOption(
            new InputOption('user-system', null, InputOption::VALUE_REQUIRED, 'The user-system to use')
        );

        $this
            ->setHelp(<<<EOT
The <info>fos:user:change-password</info> command changes the password of a user:

  <info>php app/console fos:user:change-password --user-system=acme_user matthieu</info>

This interactive shell will first ask you for a password.

You can alternatively specify the password as a second argument:

  <info>php app/console fos:user:change-password --user-system=acme_user matthieu mypassword</info>

EOT
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var UserDiscriminatorInterface $discriminator */
        $discriminator = $this->getContainer()->get('rollerworks_multi_user.user_discriminator');
        $discriminator->setCurrentUser($input->getOption('user-system'));

        parent::interact($input, $output);
    }
}
