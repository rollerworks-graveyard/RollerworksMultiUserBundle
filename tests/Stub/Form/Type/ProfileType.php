<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Stub\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType;

class ProfileType extends ProfileFormType
{
    public function getName()
    {
        return 'acme_user_profile';
    }
}
