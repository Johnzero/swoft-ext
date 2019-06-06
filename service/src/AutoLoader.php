<?php declare(strict_types=1);

namespace Swoft\service;

use Swoft\Bean\BeanFactory;
use Swoft\Helper\ComposerJSON;
use Swoft\SwoftComponent;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Rpc\Packet;
use Swoft\Rpc\Server\Swoole\CloseListener;
use Swoft\Rpc\Server\Swoole\ConnectListener;
use Swoft\Rpc\Server\Swoole\ReceiveListener;
use Swoft\Server\Swoole\SwooleEvent;
use Swoft\Rpc\Server\ServiceServer;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader extends SwoftComponent
{
    protected $container;

    /**
     * Get namespace and dir
     *
     * @return array
     * [
     *     namespace => dir path
     * ]
     */
    public function getPrefixDirs(): array
    {
        return [__NAMESPACE__ => __DIR__];
    }

    /**
     * Metadata information for the component.
     *
     * @return array
     * @see ComponentInterface::getMetadata()
     */
    public function metadata(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function beans(): array
    {
        return [
            'loginServer' => [
                'class' => ServiceServer::class,
                'port'    => 8444,
                'on'    => [
                    SwooleEvent::CONNECT => \bean(ConnectListener::class),
                    SwooleEvent::CLOSE   => \bean(CloseListener::class),
                    SwooleEvent::RECEIVE => \bean(ReceiveListener::class),
                ]
            ],
            'loginServerPacket' => [
                'class' => Packet::class
            ]
        ];
    }
}
