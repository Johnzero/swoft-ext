<?php

namespace Swoft\Admin\Bean\Collector;

use Swoft\Admin\Admin;
use Swoft\Admin\Bean\Annotation\AdminRepository;
use Swoft\Bean\Contract\HandlerInterface;

/**
 * The collector Repository
 */
class AdminRepositoryCollector
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
        if ($objectAnnotation instanceof AdminRepository) {
            $value = $objectAnnotation->getValue();
            // ----App\Controllers\Admin\UsersController----App\Admin\Repositories\Users"
            if ($value) {
                Admin::registerRepository($value, $className);
            }
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