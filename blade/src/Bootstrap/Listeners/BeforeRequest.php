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
        if ($namespaces = config('dashboard.namespaces')) {
            foreach ((array)$namespaces as $namespace => &$path) {
                $factory->addNamespace($namespace, alias($path));
            }
        }

        Assets::setAlias([
            'dashboard' => alias(config('dashboard.assets-path')),
            'admin' => alias(config('dashboard.assets-path'))
        ]);

        // 注册静态资源目录
        if ( $paths = config('dashboard.assets-path') ) {
            foreach ((array)$paths as &$path) {
                HttpFileReader::addAssetsPath($path);
            }
        }

    }
}
