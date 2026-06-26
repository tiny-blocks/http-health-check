<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Outcome of a single {@see HealthCheck} execution, pairing a {@see Status} with an optional detail.
 */
final readonly class Health
{
    private function __construct(public Status $status, public ?string $detail = null)
    {
    }

    /**
     * Builds a Health with UP status.
     *
     * @param string|null $detail An optional detail describing the healthy state.
     * @return Health The UP outcome.
     */
    public static function up(?string $detail = null): Health
    {
        return new Health(status: Status::UP, detail: $detail);
    }

    /**
     * Builds a Health with DOWN status.
     *
     * @param string|null $detail An optional detail describing the failure.
     * @return Health The DOWN outcome.
     */
    public static function down(?string $detail = null): Health
    {
        return new Health(status: Status::DOWN, detail: $detail);
    }

    /**
     * Tells whether the outcome reported UP.
     *
     * @return bool True when the status is UP, false otherwise.
     */
    public function isUp(): bool
    {
        return $this->status->isUp();
    }

    /**
     * Tells whether the outcome reported DOWN.
     *
     * @return bool True when the status is DOWN, false otherwise.
     */
    public function isDown(): bool
    {
        return $this->status->isDown();
    }
}
