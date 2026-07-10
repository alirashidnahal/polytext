<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Tests\Unit\Drivers;

use Alirashidnahal\Polytext\Drivers\Text\PlainTextDriver;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Tests\TestCase;

final class PlainTextDriverTest extends TestCase
{
    public function test_it_round_trips_plain_text(): void
    {
        $driver = new PlainTextDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.txt')));

        self::assertGreaterThanOrEqual(2, $document->nodes()->count());

        $document->mapText(static fn ($node) => strtoupper($node->text()));

        $outputPath = $this->tempFilePath('txt');
        $document->save($outputPath);

        $roundTrip = $driver->read(Source::fromPath($outputPath));
        self::assertStringContainsString('HELLO POLYTEXT', $roundTrip->text());

        unlink($outputPath);
    }
}
