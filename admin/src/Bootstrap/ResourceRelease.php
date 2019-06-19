<?php

namespace Swoft\Admin\Bootstrap;

use Swoft\Admin\Admin;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\AppEvent;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;

/**
 * 释放资源
 *
 * @Listener(AppEvent::RESOURCE_RELEASE)
 */
class ResourceRelease implements EventHandlerInterface
{
    /**
     * @param \Swoft\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        Admin::release();
    }
}
