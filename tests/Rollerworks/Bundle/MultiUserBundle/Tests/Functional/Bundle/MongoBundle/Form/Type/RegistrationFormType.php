<?php

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Bundle\MongoBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function getName()
    {
        return 'acme_mongo_registration';
    }
}
