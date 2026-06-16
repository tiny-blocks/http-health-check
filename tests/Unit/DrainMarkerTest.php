<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\DrainMarker;

final class DrainMarkerTest extends TestCase
{
    private string $markerFile = '';

    protected function tearDown(): void
    {
        if ($this->markerFile !== '' && file_exists($this->markerFile)) {
            unlink($this->markerFile);
        }
    }

    public function testIsDrainingWhenMarkerFileExistsThenReturnsTrue(): void
    {
        /** @Given a path to an existing marker file */
        $this->markerFile = (string) tempnam(sys_get_temp_dir(), 'drain');

        /** @When checking whether the marker reports draining */
        $draining = DrainMarker::from(path: $this->markerFile)->isDraining();

        /** @Then it reports draining */
        self::assertTrue($draining);
    }

    public function testIsDrainingWhenMarkerFileAbsentThenReturnsFalse(): void
    {
        /** @Given a temporary marker file */
        $path = (string) tempnam(sys_get_temp_dir(), 'drain');

        /** @And the marker file is removed */
        unlink($path);

        /** @When checking whether the marker reports draining */
        $draining = DrainMarker::from(path: $path)->isDraining();

        /** @Then it reports not draining */
        self::assertFalse($draining);
    }

    public function testDefaultThenReportsNotDrainingWhenDefaultPathIsAbsent(): void
    {
        /** @When checking whether the default marker reports draining */
        $draining = DrainMarker::default()->isDraining();

        /** @Then it reports not draining */
        self::assertFalse($draining);
    }
}
