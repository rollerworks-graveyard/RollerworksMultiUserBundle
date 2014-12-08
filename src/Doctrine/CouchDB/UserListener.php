<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Doctrine\CouchDB;

use Doctrine\ODM\CouchDB\Event;
use Doctrine\ODM\CouchDB\Event\LifecycleEventArgs;
use FOS\UserBundle\Model\UserInterface;
use Rollerworks\Bundle\MultiUserBundle\Doctrine\AbstractUserListener;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Christophe Coevoet <stof@notk.org>
 */
class UserListener extends AbstractUserListener
{
    public function getSubscribedEvents()
    {
        return array(
            Event::prePersist,
            Event::preUpdate,
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
     * @param LifecycleEventArgs $args
     */
    public function preUpdate($args)
    {
        $object = $args->getDocument();
        if ($object instanceof UserInterface) {
            $this->updateUserFields($object);
        }
    }
}
