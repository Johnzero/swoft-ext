<?php

namespace Swoft\Blade\Bootstrap\Middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
// use Swoft\Http\Server\Contract\MiddlewareInterface;
use Swoft\Support\HttpFileReader;

/**
 * @Bean()
 */
class AssetsMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    protected $registed;

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return HttpFileReader::read() ?: $handler->handle($request);
    }

}