<?php

namespace Swoft\Blade\Bootstrap\Listeners;

use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Event\ListenerRegister;

/**
 * Class ReleaseResource
 * @package Swoft\Blade\Bootstrap\Listeners
 * @Listener()
 */
class ReleaseResource implements EventHandlerInterface
{
    /**
     * @param \Swoft\Event\EventInterface $event
     */
    public function handle(EventInterface $event):void
    {

        blade_factory()->release();

    }

    //2019/05/18-08:31:06 [WARNING] Swoft\Processor\EnvProcessor:handle(33) Env file(/www/wwwroot/bakswoft4/.env) is not exist! skip load it
    //PHP Fatal error:  Uncaught Doctrine\Common\Annotations\AnnotationException: [Semantical Error] The annotation "@Swoft\Bean\Annotation\Listener" in class Swoft\Blade\Bootstrap\Listeners\ReleaseResource does not exist, or could not be auto-loaded. in /www/wwwroot/bakswoft4/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationException.php:54


}
