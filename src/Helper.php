<?php

namespace AjDic;

use Closure;
use ReflectionNamedType;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionClass;
use InvalidArgumentException;

/**
 * @internal
 */
class Helper
{
    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     *
     * @param  mixed  $value
     * @return array
     */
    public static function arrayWrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Return the default value of the given value.
     *
     *
     * @param  mixed  $value
     * @param  mixed  ...$args
     * @return mixed
     */
    public static function unwrapIfClosure($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }

    /**
     * Get the class name of the given parameter's type, if possible.
     *
     *
     * @param  \ReflectionParameter  $parameter
     * @return string|null
     */
    public static function getParameterClassName($parameter, $exemptEnum = false)
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        if ($exemptEnum) {
            if(enum_exists($parameter->getType()->getName())) {
                return null;
            }
        }

        $name = $type->getName();

        if (! is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }

    public static function namedParamatersToArry($reflector, array $args)
    {
        if (! array_is_list($args)) {
            return $args;
        }

        $reflector = static::getReflector($reflector);
        if ($reflector instanceof ReflectionClass) {
            $getConstructor = $reflector->getConstructor();

            if ( (bool) $getConstructor ) {
                $parameters = $getConstructor->getParameters();
            } else {
                $parameters = [];
            }

        } else {
            $parameters = $reflector->getParameters();    
        }
        
        $arr = [];
        
        foreach ($parameters as $key => $parameter) {

            $className = static::getParameterClassName($parameter, true);

            if (null !== $className) {

                $addToArr = true;
                $hasDefaultVal = false;

                if (isset($args[$key]) && is_object($args[$key])) {
                    if (get_class($args[$key]) == $className || $args[$key] instanceof $className) {
                        $addToArr = false;
                    }
                } else {
                    if($parameter->isDefaultValueAvailable()) {
                        $hasDefaultVal = true;
                        $arr[$parameter->getName()] = $parameter->getDefaultValue();
                    }
                }

                if ($addToArr) {
                    if (! $hasDefaultVal) {
                        array_splice($args, $parameter->getPosition(), 0, $className);
                    }
                } else {
                    $arr[$parameter->getName()] = $args[$key];
                }
            }
        }

        foreach ($parameters as $key => $parameter) {
            $position = $parameter->getPosition();

            if (array_key_exists($position, $args)) {

                $className = static::getParameterClassName($parameter, true);
                
                if(null === $className) {
                    $arr[$parameter->getName()] = $args[$position];
                }
            }
        }
        
        return $arr;
    }

    public static function getReflector($reflector)
    {
        if (is_array($reflector)) {
            if (! \method_exists($reflector[0], $reflector[1])) {
                throw new InvalidArgumentException('Invalid Reflector.');
            }

            return new ReflectionMethod(get_class($reflector[0]), $reflector[1]);
        }

        if ($reflector instanceof Closure) {
            return new ReflectionFunction($reflector);
        }

        if (\is_string($reflector)) {
            if (\class_exists($reflector)) {
                return new ReflectionClass($reflector);
            }

            if (\function_exists($reflector)) {
                return new ReflectionFunction($reflector);
            }
        }

        throw new InvalidArgumentException('Invalid Reflector');
    }
}
