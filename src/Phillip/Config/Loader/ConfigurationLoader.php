<?php

namespace Phillip\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Joshua Estes
 */
class ConfigurationLoader extends FileLoader
{

    /**
     * @return array
     */
    public function load($resource, $type = null)
    {
        return Yaml::parse($resource);
    }

    public function supports($resource, $type = null)
    {
        return true;
    }

}
