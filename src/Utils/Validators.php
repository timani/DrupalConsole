<?php

/**
 * @file
 * Contains \Drupal\AppConsole\Utils\Validators.
 */

namespace Drupal\AppConsole\Utils;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperInterface;
use Drupal\Core\Cache\Cache;

class Validators extends Helper implements HelperInterface
{
    private $caches = [];

    const REGEX_CLASS_NAME = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/';
    const REGEX_MACHINE_NAME = '/^[a-z0-9_]+$/';
    // This REGEX remove spaces between words
    const REGEX_REMOVE_SPACES = '/[\\s+]/';

    public function __construct()
    {
    }

    public function validateModuleName($module)
    {
        if (!empty($module)) {
            return $module;
        } else {
            throw new \InvalidArgumentException(sprintf('Module name "%s" is invalid.', $module));
        }
    }

    public function validateClassName($class_name)
    {
        if (preg_match(self::REGEX_CLASS_NAME, $class_name)) {
            return $class_name;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class name "%s" is invalid, it must starts with a letter or underscore, followed by any number of letters, numbers, or underscores.',
                    $class_name
                )
            );
        }
    }

    public function validateMachineName($machine_name)
    {
        if (preg_match(self::REGEX_MACHINE_NAME, $machine_name)) {
            return $machine_name;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Machine name "%s" is invalid, it must contain only lowercase letters, numbers and underscores.',
                    $machine_name
                )
            );
        }
    }

    public function validateModulePath($module_path, $create = false)
    {
        if (!is_dir($module_path)) {
            if ($create && mkdir($module_path, 0755, true)) {
                return $module_path;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Module path "%s" is invalid. You need to provide a valid path.',
                    $module_path
                )
            );
        }

        return $module_path;
    }

    public function validateModuleDependencies($dependencies)
    {
        $dependencies_checked = array(
          'success' => array(),
          'fail' => array(),
        );

        if (empty($dependencies)) {
            return array();
        }

        $dependencies = explode(',', $this->removeSpaces($dependencies));
        foreach ($dependencies as $key => $module) {
            if (!empty($module)) {
                if (preg_match(self::REGEX_MACHINE_NAME, $module)) {
                    $dependencies_checked['success'][] = $module;
                } else {
                    $dependencies_checked['fail'][] = $module;
                }
            }
        }

        return $dependencies_checked;
    }

    /**
     * Validate if module name exist.
     *
     * @param string $module  Module name
     * @param array  $modules List of modules
     *
     * @return string
     */
    public function validateModuleExist($module, $modules)
    {
        if (!in_array($module, array_values($modules))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Module "%s" is not in your application. Try generate:module to create it.',
                    $module
                )
            );
        }

        return $module;
    }

    /**
     * Validate if service name exist.
     *
     * @param string $service  Service name
     * @param array  $services Array of services
     *
     * @return string
     */
    public function validateServiceExist($service, $services)
    {
        if ($service == '') {
            return;
        }

        if (!in_array($service, array_values($services))) {
            throw new \InvalidArgumentException(sprintf('Service "%s" is invalid.', $service));
        }

        return $service;
    }

    /**
     * Validate if a string is a valid cache.
     *
     * @param string $cache The cache name
     *
     * @return mixed The cache name if valid or FALSE if not valid
     */
    public function validateCache($cache)
    {
        // Get the valid caches
        $caches = $this->getCaches();
        $cache_keys = array_keys($caches);
        $cache_keys[] = 'all';

        if (!in_array($cache, array_values($cache_keys))) {
            return false;
        }

        return $cache;
    }

    /**
     * Validates if class name have spaces between words.
     *
     * @param string $name
     *
     * @return string
     */
    public function validateSpaces($name)
    {
        $string = $this->removeSpaces($name);
        if ($string == $name) {
            return $name;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'The name "%s" is invalid, spaces between words are not allowed.',
                    $name
                )
            );
        }
    }

    public function removeSpaces($name)
    {
        return preg_replace(self::REGEX_REMOVE_SPACES, '', $name);
    }

    public function getName()
    {
        return 'validators';
    }

    /**
     * Auxiliary function to get all available drupal caches.
     *
     * @return array The all available drupal caches
     */
    public function getCaches()
    {
        if (empty($this->caches)) {
            foreach (Cache::getBins() as $name => $bin) {
                $this->caches[$name] = $bin;
            }
        }

        return $this->caches;
    }
}
