<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Bean\Annotation\AdminRepositoryListener;
use Swoft\Bean\Contract\HandlerInterface;
use Swoft\Bean\Definition\ObjectDefinition;

/**
 * The collector AdminRepositoryListener
 */
class AdminRepositoryListenerCollector
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

    // public function beforeInit(string $beanName, string $className, ObjectDefinition $objDfn, array $annotation):
    public function beforeInit(
        string $beanName, string $className, ObjectDefinition $objDfn, array $annotation
    )
    {
        if ($objDfn instanceof AdminRepositoryListener) {
            $value = $objDfn->getValue();
            if (!isset(self::$values[$value])) {
                self::$values[$value] = [];
            }

            self::$values[$value][] = $className;
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getCollector(string $repository = null)
    {
        if ($repository) {
            return isset(self::$values[$repository]) ? self::$values[$repository] : [];
        }

        return self::$values;
    }
}