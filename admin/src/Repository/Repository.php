<?php

namespace Swoft\Admin\Repository;

use function explode;

use Swoft\Http\Server\HttpContext;
use Swoft\Context\Context;
use Swoft\Bean\Container;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Contract\MiddlewareInterface;
use Swoft\Http\Server\Exception\HttpServerException;
use Swoft\Http\Server\RequestHandler;
use Swoft\Http\Server\Router\Route;
use Swoft\Http\Server\Router\Router;

trait Repository
{
    /**
     * @var array
     */
    protected static $repositories = [];

    /**
     * 获取当前控制器对应的数据仓库
     *
     * @return RepositoryInterface
     */
    public static function repository()
    {
//        $controller = RequestContext::getContextDataByKey('controllerClass');
//        consolelog(static::$repositories);
//        array:1 [
//                "App\Controllers\Admin\UsersController" => Swoft\Admin\Repository\RepositoryProxy^ {#7273
//                #repository: "App\Admin\Repositories\Users"
//                #listeners: []
//            }
//        ]
        [$controller,$action] = class_action();
//        "App\Http\Controller\Admin\UsersControlle"
        if (isset(static::$repositories[$controller])) {
            return static::$repositories[$controller];
        }
        throw new \UnexpectedValueException("Repository未定义");
    }

    /**
     * 注册Repository
     *
     * @param string $controllerClass
     * @param RepositoryInterface $repository
     */
    public static function registerRepository(string $controllerClass, string $repository)
    {
        static::$repositories[$controllerClass] = new RepositoryProxy($repository);
    }
}
