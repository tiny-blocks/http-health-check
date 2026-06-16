<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\Server\Response;

/**
 * PSR-15 handler for the readiness endpoint. Runs the configured health checks, honoring an optional drain marker.
 */
final readonly class ReadinessHandler implements RequestHandlerInterface
{
    private function __construct(private HealthChecks $checks, private ?DrainMarker $drainMarker = null)
    {
    }

    /**
     * Creates a ReadinessHandler from a configured HealthChecks set and an optional drain marker.
     *
     * @param HealthChecks $checks The checks executed on each readiness request.
     * @param DrainMarker|null $drainMarker The drain marker checked before running the checks, or null when unused.
     * @return ReadinessHandler The handler.
     */
    public static function from(HealthChecks $checks, ?DrainMarker $drainMarker = null): ReadinessHandler
    {
        return new ReadinessHandler(checks: $checks, drainMarker: $drainMarker);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!is_null($this->drainMarker) && $this->drainMarker->isDraining()) {
            return Response::serviceUnavailable(body: ['status' => Readiness::UNAVAILABLE->value]);
        }

        $report = $this->checks->report();

        if ($report->readiness()->isUnavailable()) {
            return Response::serviceUnavailable(body: $report->toArray());
        }

        return Response::ok(body: $report->toArray());
    }
}
