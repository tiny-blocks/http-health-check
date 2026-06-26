<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Filesystem drain signal: while the watched file exists, readiness reports UNAVAILABLE.
 *
 * <p>The presence probe clears PHP's stat cache before checking existence, so a marker removed
 * inside a long-running process is not reported as still present. It uses <code>file_exists</code>,
 * which reports a present marker even when the process cannot open it for reading, so an unreadable
 * marker still signals draining.</p>
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
        return file_exists($this->path);
    }
}
