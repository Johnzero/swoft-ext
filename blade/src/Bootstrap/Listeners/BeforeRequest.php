<?php

namespace Swoft\Blade\Bootstrap\Listeners;

//use Swoft\App;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;

use Swoft\Http\Server\HttpServerEvent;
use Swoft\Http\Server\Middleware\MiddlewareRegister;


// use Swoft\Http\Server\ServerDispatcher;
use Swoft\Blade\Bootstrap\Middlewares\AssetsMiddleware;
use Swoft\Support\HttpFileReader;
use Swoft\Support\Assets;

use Swoft\Http\Server\Router\Route;
use Swoft\Http\Server\Router\Router;

/**
 *
 * @Listener(HttpServerEvent::BEFORE_REQUEST)
 */
class BeforeRequest implements EventHandlerInterface
{
    /**
     * @var bool
     */
    protected static $registed;

    /**
     * @var array
     */
    protected $middlewares = [
        AssetsMiddleware::class
    ];

    /**
     * @param \Swoft\Event\EventInterface $event
     */
    public function handle(EventInterface $event):void
    {
        $factory = blade_factory();

        $factory->share('__env', $factory);
        if (static::$registed) {
            return ;
        }
        static::$registed = true;

        // 注册视图命名空间
//        var_dump(config('blade-view'));

        if ($namespaces = config('blade-view.namespaces')) {
            foreach ((array)$namespaces as $namespace => &$path) {
                $factory->addNamespace($namespace, alias($path));
            }
        }

//        $router = Router::create();
//        $r1 = Router::create('GET', '/path1', 'handler0');
//        $r1->setName('r1');
//        $router->addRoute($r1);
//
        // 判断是否允许读取静态资源
        $readAssets = 1;
        $factory->addNamespace('admin', alias(config('admin.views-path', __DIR__.'/../../resources/views')));
//        Assets::setAlias([
//            'admin' => alias(config('admin.assets-path', '/public/assets/swoft-admin'))
//        ]);
        
        // 注册静态资源目录
        if ($paths = "/public") {
            foreach ((array)$paths as &$path) {
                HttpFileReader::addAssetsPath($path);
            }
        }
        /* @var ServerDispatcher $serverDispatcher */
//        $serverDispatcher = \bean('serverDispatcher');
//        foreach ($this->middlewares as $name => $middleware) {
//            if (!$readAssets && $middleware === AssetsMiddleware::class) {
//                continue;
//            }
//            $serverDispatcher->addMiddleware(
//                class_basename($middleware),
//                is_string($name) && !empty($name) ? $name : null
//            );
//        }


    }
}
