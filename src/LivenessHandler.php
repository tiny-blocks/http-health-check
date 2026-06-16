<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\Server\Response;

/**
 * PSR-15 handler for the liveness endpoint. Responds 200 as long as the process can serve the request.
 */
final readonly class LivenessHandler implements RequestHandlerInterface
{
    private function __construct()
    {
    }

    /**
     * Creates a LivenessHandler.
     *
     * @return LivenessHandler The handler.
     */
    public static function create(): LivenessHandler
    {
        return new LivenessHandler();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return Response::ok(body: ['status' => Status::UP->value]);
    }
}
