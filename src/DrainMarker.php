<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Filesystem drain signal: while the watched file exists, readiness reports UNAVAILABLE.
 *
 * <p>The presence probe opens the file instead of relying on <code>is_file</code> so the result is
 * unaffected by PHP's stat cache, which would otherwise keep reporting a removed marker as present
 * inside long-running processes.</p>
 */
final readonly class DrainMarker
{
    private const string PATH = '/tmp/draining';

    private function __construct(private string $path)
    {
    }

    /**
     * Creates a DrainMarker watching the given path.
     *
     * @param string $path The path whose presence signals draining.
     * @return DrainMarker The drain marker.
     */
    public static function from(string $path): DrainMarker
    {
        return new DrainMarker(path: $path);
    }

    /**
     * Creates a DrainMarker watching the default path.
     *
     * @return DrainMarker The drain marker watching the default path.
     */
    public static function default(): DrainMarker
    {
        return new DrainMarker(path: self::PATH);
    }

    /**
     * Tells whether the marker file is currently present.
     *
     * @return bool True when the file exists at the path, false otherwise.
     */
    public function isDraining(): bool
    {
        $handle = @fopen($this->path, 'r');

        if ($handle === false) {
            return false;
        }

        fclose($handle);

        return true;
    }
}
