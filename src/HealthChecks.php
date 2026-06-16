<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use TinyBlocks\Collection\Collection;
use TinyBlocks\HttpHealthCheck\Internal\Assessment;

/**
 * Immutable, type-safe collection of health checks, each registered as critical or optional.
 *
 * @extends Collection<Dependency>
 */
final class HealthChecks extends Collection
{
    /**
     * Runs every registered check and reports the aggregate health.
     *
     * @return HealthReport The per-dependency results and the overall readiness.
     */
    public function report(): HealthReport
    {
        return Assessment::of(checks: $this)->run();
    }

    /**
     * Returns a copy of the HealthChecks with the check registered as critical.
     *
     * @param HealthCheck $check The check verifying the dependency.
     * @return HealthChecks A new collection including the critical check.
     */
    public function withCritical(HealthCheck $check): HealthChecks
    {
        return $this->add(Dependency::critical(check: $check));
    }

    /**
     * Returns a copy of the HealthChecks with the check registered as optional.
     *
     * @param HealthCheck $check The check verifying the dependency.
     * @return HealthChecks A new collection including the optional check.
     */
    public function withOptional(HealthCheck $check): HealthChecks
    {
        return $this->add(Dependency::optional(check: $check));
    }
}
