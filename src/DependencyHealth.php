<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Measured health of one dependency: its {@see Health}, criticality, component, and elapsed time.
 */
final readonly class DependencyHealth
{
    public function __construct(
        public ?string $name,
        public Health $health,
        public bool $critical,
        public string $component,
        public float $durationInMilliseconds
    ) {
    }

    /**
     * Returns the DependencyHealth as an associative array.
     *
     * @return array<string, mixed> The check result keyed by field.
     */
    public function toArray(): array
    {
        $payload = [];

        if (!is_null($this->name)) {
            $payload['name'] = $this->name;
        }

        if (!is_null($this->health->detail)) {
            $payload['detail'] = $this->health->detail;
        }

        $payload['status'] = $this->health->status->value;
        $payload['critical'] = $this->critical;
        $payload['component'] = $this->component;
        $payload['duration_in_milliseconds'] = $this->durationInMilliseconds;

        return $payload;
    }

    /**
     * Tells whether this is a critical dependency reporting DOWN.
     *
     * @return bool True when the dependency is critical and down, false otherwise.
     */
    public function isCriticalFailure(): bool
    {
        return $this->critical && $this->health->isDown();
    }

    /**
     * Tells whether this is an optional dependency reporting DOWN.
     *
     * @return bool True when the dependency is optional and down, false otherwise.
     */
    public function isOptionalFailure(): bool
    {
        return !$this->critical && $this->health->isDown();
    }
}
