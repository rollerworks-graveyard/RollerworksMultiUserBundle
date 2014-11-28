<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Command;

use FOS\UserBundle\Command\DeactivateUserCommand as BaseDeactivateUserCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class DeactivateUserCommand extends BaseDeactivateUserCommand
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
The <info>fos:user:deactivate</info> command deactivates a user (will not be able to log in)

  <info>php app/console fos:user:deactivate --user-system=acme_user matthieu</info>
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
