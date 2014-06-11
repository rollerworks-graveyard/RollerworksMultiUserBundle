<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\MongoBundle\Form\Type;

use FOS\UserBundle\Form\Type\ResettingFormType  as BaseType;

class ResettingFormType extends BaseType
{
    public function getName()
    {
        return 'acme_user_resetting';
    }
}
