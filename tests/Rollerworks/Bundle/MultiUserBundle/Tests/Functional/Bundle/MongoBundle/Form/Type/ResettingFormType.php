<?php

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\ResettingFormType  as BaseType;

class ResettingFormType extends BaseType
{
    public function getName()
    {
        return 'acme_user_resetting';
    }
}
