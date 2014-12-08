<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResettingFormType extends AbstractType
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @param string $class    The User class name
     * @param string $typeName The FormType name
     */
    public function __construct($class, $typeName)
    {
        $this->class = $class;
        $this->typeName = $typeName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array('label' => 'form.new_password'),
            'second_options' => array('label' => 'form.new_password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'resetting',
        ));
    }

    public function getName()
    {
        return $this->typeName;
    }
}
