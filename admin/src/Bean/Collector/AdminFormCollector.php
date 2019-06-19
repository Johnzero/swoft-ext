<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Form;
use Swoft\Admin\Bean\Annotation\AdminForm;
use Swoft\Bean\Contract\HandlerInterface;

/**
 * The collector form
 */
class AdminFormCollector implements HandlerInterface
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
    )
    {
        if ($objectAnnotation instanceof AdminForm) {
            $value = $objectAnnotation->getName();

            if (! $value) $value = lcfirst(class_basename($className));

            Form::extend($value, $className);

            self::$values[$className] = $value;
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getCollector()
    {
        return self::$values;
    }
}