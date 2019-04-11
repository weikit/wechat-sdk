<?php

namespace Weikit\Wechat\Sdk;

class BaseObject
{
    public function __construct(array $config = [])
    {
        __configure($this, $config);
        $this->init();
    }

    public function init()
    {
    }
}

/**
 * @param object $object
 * @param array $properties
 */
function __configure($object, $properties)
{
    foreach ($properties as $name => $value) {
        $object->$name = $value;
    }
}