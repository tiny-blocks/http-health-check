<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use Throwable;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\HealthCheck;

final readonly class HealthCheckFake implements HealthCheck
{
    private function __construct(
        private ?string $name,
        private Health $health,
        private ?Throwable $failure,
        private string $component
    ) {
    }

    public static function withHealth(
        Health $health,
        ?string $name = null,
        string $component = 'fake'
    ): HealthCheckFake {
        return new HealthCheckFake(name: $name, health: $health, failure: null, component: $component);
    }

    public static function withFailure(
        Throwable $failure,
        ?string $name = null,
        string $component = 'fake'
    ): HealthCheckFake {
        return new HealthCheckFake(name: $name, health: Health::up(), failure: $failure, component: $component);
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function check(): Health
    {
        if (!is_null($this->failure)) {
            throw $this->failure;
        }

        return $this->health;
    }

    public function component(): string
    {
        return $this->component;
    }
}
