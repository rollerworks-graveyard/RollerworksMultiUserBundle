<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Doctrine\Orm;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Rollerworks\Bundle\MultiUserBundle\Doctrine\AbstractUserListener;

/**
 * Doctrine ORM listener updating the canonical fields and the password.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Christophe Coevoet <stof@notk.org>
 */
class UserListener extends AbstractUserListener
{
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist($args)
    {
        $object = $args->getEntity();
        if ($object instanceof UserInterface) {
            $this->updateUserFields($object);
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate($args)
    {
        $object = $args->getEntity();
        if ($object instanceof UserInterface) {
            $this->updateUserFields($object);
            // We are doing a update, so we must force Doctrine to update the
            // changeset in case we changed something above
            $em   = $args->getEntityManager();
            $uow  = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($object));
            $uow->recomputeSingleEntityChangeSet($meta, $object);
        }
    }
}
