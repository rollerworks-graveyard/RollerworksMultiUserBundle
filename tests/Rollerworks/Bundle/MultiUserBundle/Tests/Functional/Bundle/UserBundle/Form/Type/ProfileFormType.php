<?php

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    public function getName()
    {
        return 'acme_user_profile';
    }
}
