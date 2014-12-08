<?php

/**
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) 2013 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Routing;

use Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Changes the route name to a userDiscriminated version
 * and delegates to the real generator.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class UserDiscriminatorUrlGenerator implements RouterInterface
{
    private $userDiscriminator;
    private $generator;
    private $prefix;
    private $prefixLen;

    /**
     * @param UserDiscriminatorInterface $userDiscriminator
     * @param UrlGeneratorInterface      $generator
     * @param string                     $prefix
     */
    public function __construct(UserDiscriminatorInterface $userDiscriminator, UrlGeneratorInterface $generator, $prefix = 'fos_user')
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->generator = $generator;
        $this->prefix = $prefix;
        $this->prefixLen = strlen($prefix);
    }

    /**
     * @codeCoverageIgnore
     */
    public function setContext(RequestContext $context)
    {
        // noop
    }

    /**
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function getContext()
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (substr($name, 0, $this->prefixLen) === $this->prefix) {
            $name = $this->userDiscriminator->getCurrentUserConfig()->getRoutePrefix().substr($name, $this->prefixLen);
        }

        return $this->generator->generate($name, $parameters, $referenceType);
    }

    /**
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function getRouteCollection()
    {
        // noop
    }

    /**
     * @param string $pathinfo
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function match($pathinfo)
    {
        // noop
    }
}
