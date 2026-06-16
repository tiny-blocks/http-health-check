<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Aggregate readiness derived from the combined outcome of all registered health checks.
 */
enum Readiness: string
{
    case HEALTHY = 'HEALTHY';
    case DEGRADED = 'DEGRADED';
    case UNAVAILABLE = 'UNAVAILABLE';

    /**
     * Tells whether every critical check is healthy and no check is degraded.
     *
     * @return bool True when the readiness is HEALTHY, false otherwise.
     */
    public function isHealthy(): bool
    {
        return $this === Readiness::HEALTHY;
    }

    /**
     * Tells whether the service is operational with at least one optional check down.
     *
     * @return bool True when the readiness is DEGRADED, false otherwise.
     */
    public function isDegraded(): bool
    {
        return $this === Readiness::DEGRADED;
    }

    /**
     * Tells whether at least one critical check is down.
     *
     * @return bool True when the readiness is UNAVAILABLE, false otherwise.
     */
    public function isUnavailable(): bool
    {
        return $this === Readiness::UNAVAILABLE;
    }
}
