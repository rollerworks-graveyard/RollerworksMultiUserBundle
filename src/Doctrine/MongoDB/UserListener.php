<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Doctrine\MongoDB;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use FOS\UserBundle\Model\UserInterface;
use Rollerworks\Bundle\MultiUserBundle\Doctrine\AbstractUserListener;

/**
 * Doctrine MongoDB ODM listener updating the canonical fields and the password.
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
        $object = $args->getDocument();
        if ($object instanceof UserInterface) {
            $this->updateUserFields($object);
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate($args)
    {
        $object = $args->getDocument();
        if ($object instanceof UserInterface) {
            $this->updateUserFields($object);
            // We are doing a update, so we must force Doctrine to update the
            // changeset in case we changed something above
            $dm = $args->getDocumentManager();
            $uow = $dm->getUnitOfWork();
            $meta = $dm->getClassMetadata(get_class($object));
            $uow->recomputeSingleDocumentChangeSet($meta, $object);
        }
    }
}
