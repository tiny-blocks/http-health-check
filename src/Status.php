<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Binary health state reported by a single {@see HealthCheck}.
 */
enum Status: string
{
    case UP = 'UP';
    case DOWN = 'DOWN';

    /**
     * Tells whether the state is UP.
     *
     * @return bool True when the state is UP, false otherwise.
     */
    public function isUp(): bool
    {
        return $this === Status::UP;
    }

    /**
     * Tells whether the state is DOWN.
     *
     * @return bool True when the state is DOWN, false otherwise.
     */
    public function isDown(): bool
    {
        return $this === Status::DOWN;
    }
}
