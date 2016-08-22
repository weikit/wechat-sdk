<?php

if (!file_exists('configure')) {
    /**
     * 配置object
     *
     * @param $object
     * @param $properties
     * @return mixed
     */
    function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }
}

if (!function_exists('createObject')) {
    /**
     * 创建Object
     *
     * @param $config
     * @param array $params
     * @return mixed
     */
    function createObject($config, array $params = array())
    {
        if (is_string($config)) {
            if ($params === array()) {
                return new $config();
            } else {
                $reflection = new ReflectionClass($config);
                return $reflection->newInstanceArgs($params);
            }
        } elseif (is_array($config) && isset($config['class'])) {
            $class = $config['class'];
            unset($config['class']);
            if ($params === array()) {
                $object = new $class($config);
            } else {
                $reflection = new ReflectionClass($class);
                $params[count($params)] = $config;
                $object = $reflection->newInstanceArgs($params);
            }
            return $object;
        } elseif (is_callable($config, true)) {
            return call_user_func_array($config, $params);
        } elseif (is_array($config)) {
            throw new \InvalidArgumentException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new \InvalidArgumentException('Unsupported configuration type: ' . gettype($config));
        }
    }
}