<?php

namespace Swoft\Blade\Bootstrap;

//use Swoft\Bean\Annotation\ServerListener;
use Swoft\Event\Annotation\Mapping\Listener;

use Swoft\Bean\BeanFactory;
use Swoft\Bean\ObjectDefinition;
use Swoft\Bean\Resource\ServerAnnotationResource;
//use Swoft\Bootstrap\Listeners\Interfaces\WorkerStartInterface;


use Swoft\Http\Message\Request;
use Swoft\Server\ServerInterface;
use Swoft\Server\Swoole\SwooleEvent;

use Swoft\Support\Assets;
use Swoole\Http\Response;
use Swoole\Server;
use Swoft\Server\Swoole\RequestInterface;

/**
 * @Listener(SwooleEvent::WORKER_START)
 */
//class Bootstrap extends ServerInterface
//{
//    public function onWorkerStart(Server $server, int $workerId)
//    {
//        // 注册视图命名空间
//        blade_factory()->addNamespace('admin', alias(config('admin.views-path', __DIR__.'/../../resource/views')));
//
//        // 注册静态资源别名
//        Assets::setAlias([
//            'admin' => alias(config('admin.assets-path', '/assets/swoft-admin'))
//        ]);
//
//    }
//}
