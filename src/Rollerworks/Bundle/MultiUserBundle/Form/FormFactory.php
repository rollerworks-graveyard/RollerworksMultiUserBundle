<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Form;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Leonardo Proietti <leonardo.proietti@gmail.com>
 */
class FormFactory implements FactoryInterface
{
    /**
     * @var UserDiscriminatorInterface
     */
    private $userDiscriminator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     */
    private $type;

    /**
     * @param FormFactoryInterface $formFactory
     * @param string               $type        Form type name
     */
    public function __construct(FormFactoryInterface $formFactory, $type)
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
    }

    /**
     * @param UserDiscriminatorInterface $userDiscriminator
     */
    public function setUserDiscriminator($userDiscriminator)
    {
        $this->userDiscriminator = $userDiscriminator;
    }

    /**
     * @return FormInterface
     */
    public function createForm()
    {
        $user = $this->userDiscriminator->getCurrentUserConfig();
        $type = $user->getFormType($this->type);
        $name = $user->getFormName($this->type);
        $validationGroups = $user->getFormValidationGroups($this->type);

        return $this->formFactory->createNamed($name, $type, null, array('validation_groups' => $validationGroups));
    }
}
