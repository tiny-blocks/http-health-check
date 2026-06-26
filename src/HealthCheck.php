<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Verifies the health of a single dependency the service relies on.
 *
 * <p>Each implementation checks one external resource and reports its {@see Health}. The
 * {@see component()} is the generic category exposed in the report (for example "database"),
 * never a specific connection or host name. The optional {@see name()} distinguishes checks that
 * share the same component. Implementations catch their own infrastructure failures and return a
 * DOWN {@see Health} rather than letting the exception escape.</p>
 */
interface HealthCheck
{
    /**
     * Returns the optional discriminator that distinguishes this check from others sharing its component.
     *
     * @return string|null The discriminator (for example "primary"), or null when not set.
     */
    public function name(): ?string;

    /**
     * Checks the dependency and reports its current health.
     *
     * @return Health The UP or DOWN outcome of the check.
     */
    public function check(): Health;

    /**
     * Returns the generic component category exposed in the report.
     *
     * @return string A short, stable, safe-to-expose category (for example "database").
     */
    public function component(): string;
}
