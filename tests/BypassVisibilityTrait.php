<?php

namespace DBCheckerTests;

trait BypassVisibilityTrait
{
    protected function getMethod($instance, string $method) : \ReflectionMethod
    {
        $reflector = new \ReflectionClass(get_class($instance));
        $method = $reflector->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    protected function callMethod($instance, string $method, $arguments=[])
    {
        $method = $this->getMethod($instance, $method);
        return $method->invokeArgs($instance, $arguments);
    }
}