<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Model;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\GroupManagerInterface;
use Rollerworks\Bundle\MultiUserBundle\Exception\MissingTemplateException;

/**
 * UserConfiguration, holds the configuration of a user in the system.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class UserConfig
{
    protected $servicePrefix;
    protected $routePrefix;
    protected $templates;
    protected $forms;
    protected $config;

    protected $userManager;
    protected $groupManager;

    /**
     * Constructor.
     *
     * @param string                $servicePrefix
     * @param string                $routePrefix
     * @param UserManagerInterface  $userManager
     * @param GroupManagerInterface $groupManager
     */
    public function __construct($servicePrefix, $routePrefix, UserManagerInterface $userManager, GroupManagerInterface $groupManager)
    {
        $this->servicePrefix = $servicePrefix;
        $this->routePrefix = $routePrefix;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->templates = array();
        $this->forms = array();
        $this->config = array();
    }

    /**
     * Set a config.
     *
     * @param string                       $name
     * @param string|integer|boolean|array $value
     */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * @param string                       $name
     * @param string|integer|boolean|array $defaultValue
     *
     * @return string|integer|boolean|array
     */
    public function getConfig($name, $defaultValue = null)
    {
        return array_key_exists($name, $this->config) ? $this->config[$name] : $defaultValue;
    }

    /**
     * Set the form configuration.
     *
     * @param string $name
     * @param string $formName
     * @param string $type
     * @param array  $validationGroups
     *
     * @return static
     */
    public function setForm($name, $formName, $type, array $validationGroups)
    {
        $this->forms[$name] = array('type' => $type, 'name' => $formName, 'validation_groups' => $validationGroups);

        return $this;
    }

    /**
     * Set the template configuration.
     *
     * @param string $name
     * @param string $resource
     *
     * @return static
     */
    public function setTemplate($name, $resource)
    {
        $this->templates[$name] = $resource;

        return $this;
    }

    /**
     * Get the form-type name for the given config-name.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getFormType($name)
    {
        return isset($this->forms[$name]['type']) ? $this->forms[$name]['type'] : null;
    }

    /**
     * Get the form name for the given config-name.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getFormName($name)
    {
        return isset($this->forms[$name]['name']) ? $this->forms[$name]['name'] : null;
    }

    /**
     * Get the validation groups for the given config-name.
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getFormValidationGroups($name)
    {
        return isset($this->forms[$name]['validation_groups']) ? $this->forms[$name]['validation_groups'] : null;
    }

    /**
     * Get the View template for the given config-name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getTemplate($name)
    {
        if (!isset($this->templates[$name])) {
            throw new MissingTemplateException(sprintf('Unable to get template for "%s", there is no such template configured.', $name));
        }

        $template = (string) $this->templates[$name];
        if ('' === $template) {
            throw new MissingTemplateException(sprintf('Unable to get template for "%s", it seems the template value is empty. Make sure you enabled the proper section and configured a proper value.', $name));
        }

        return $template;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    /**
     * @return GroupManagerInterface
     */
    public function getGroupManager()
    {
        return $this->groupManager;
    }

    /**
     * @return string
     */
    public function getServicePrefix()
    {
        return $this->servicePrefix;
    }

    /**
     * @return string
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }
}
