<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
