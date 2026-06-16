<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use TinyBlocks\Collection\Collection;

/**
 * Collection of per-dependency results that reduces to the aggregate readiness.
 *
 * @extends Collection<DependencyHealth>
 */
final class DependencyHealths extends Collection
{
    /**
     * Reduces the per-dependency results into the aggregate readiness.
     *
     * @return Readiness UNAVAILABLE on any critical failure, DEGRADED on any optional failure, otherwise HEALTHY.
     */
    public function readiness(): Readiness
    {
        $criticalFailures = $this->filter(
            static fn(DependencyHealth $dependency): bool => $dependency->isCriticalFailure()
        );

        if (!$criticalFailures->isEmpty()) {
            return Readiness::UNAVAILABLE;
        }

        $optionalFailures = $this->filter(
            static fn(DependencyHealth $dependency): bool => $dependency->isOptionalFailure()
        );

        return $optionalFailures->isEmpty() ? Readiness::HEALTHY : Readiness::DEGRADED;
    }
}
