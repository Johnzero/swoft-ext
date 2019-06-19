<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Admin;
use Swoft\Admin\Bean\Annotation\AdminNavbar;
use Swoft\Bean\Contract\HandlerInterface;
use Swoft\Bean\Definition\ObjectDefinition;

/**
 * The collector navbar
 */
class AdminNavbarCollector implements HandlerInterface
{
    /**
     * @var array
     */
    private static $values = [];

    /**
     * @param string $className
     * @param object $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param null   $propertyValue
     *
     * @return mixed
     */
    public function beforeInit(
        string $beanName, string $className, ObjectDefinition $objDfn, array $annotation
    ): void
    {
        if ($objectAnnotation instanceof AdminNavbar) {
            self::$values = $className;
        }
    }

    /**
     * @return array
     */
    public static function getCollector()
    {
        return self::$values;
    }

    public function classProxy(string $className): string;
    
    public function getReferenceValue($value);

}