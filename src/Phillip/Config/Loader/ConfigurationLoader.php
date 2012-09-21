<?php

namespace Phillip\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader extends FileLoader
{

    public function load($resource, $type = null)
    {
        return Yaml::parse($resource);
    }

    public function supports($resource, $type = null)
    {
        return true;
    }
}
