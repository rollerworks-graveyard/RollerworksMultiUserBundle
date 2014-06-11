<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Rollerworks\Component\SfContainerInjector\ContainerInjector;

/**
 * Base Doctrine listener updating the canonical username and password fields.
 *
 * Overwritten by database specific listeners to register the right events and
 * to let the UoW recalculate the change set if needed.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Christophe Coevoet <stof@notk.org>
 * @author David Buchmann <mail@davidbu.ch>
 */
abstract class AbstractUserListener implements EventSubscriber
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var UserDiscriminatorInterface
     */
    private $userDiscriminator;

    /**
     * @var ContainerInjector
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInjector $container
     */
    public function __construct(ContainerInjector $container)
    {
        $this->container = $container;
    }

    /**
     * {inheritdoc}
     */
    public function prePersist($args)
    {
        if (null === $this->userDiscriminator->getCurrentUser()) {
            return ;
        }

        $object = $args->getObject();
        if ($object instanceof UserInterface && $this->userDiscriminator->getCurrentUserConfig()->getConfig('use_listener', true)) {
            $this->updateUserFields($object);
        }
    }

    /**
     * Pre update listener based on doctrine commons, overwrite to update
     * the changeset in the UoW and to handle non-common event argument
     * class.
     *
     * @param LifecycleEventArgs $args weak typed to allow overwriting
     */
    public function preUpdate($args)
    {
        if (null === $this->userDiscriminator->getCurrentUser()) {
            return ;
        }

        $object = $args->getObject();
        if ($object instanceof UserInterface && $this->userDiscriminator->getCurrentUserConfig()->getConfig('use_listener', true)) {
            $this->updateUserFields($object);
        }
    }

    /**
     * This must be called on prePersist and preUpdate if the event is about a
     * user.
     *
     * @param UserInterface $user
     */
    protected function updateUserFields(UserInterface $user)
    {
        if (null === $this->userDiscriminator) {
            $this->userDiscriminator = $this->container->get('rollerworks_multi_user.user_discriminator');
        }

        // Can only use the user manager when there is an user-system active
        if (null === $this->userDiscriminator->getCurrentUser() || true !== $this->userDiscriminator->getCurrentUserConfig()->getConfig('use_listener', true)) {
            return ;
        }

        if (null === $this->userManager) {
            $this->userManager = $this->container->get('fos_user.user_manager');
        }

        $this->userManager->updateCanonicalFields($user);
        $this->userManager->updatePassword($user);
    }
}
