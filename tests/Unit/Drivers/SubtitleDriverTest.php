<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Tests\Unit\Drivers;

use Alirashidnahal\Polytext\Drivers\Subtitle\SrtDriver;
use Alirashidnahal\Polytext\Drivers\Subtitle\VttDriver;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Tests\TestCase;

final class SubtitleDriverTest extends TestCase
{
    public function test_srt_round_trip_preserves_timing(): void
    {
        $driver = new SrtDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.srt')));

        $document->replaceText('Hello world', 'Salam world');

        $output = $document->toString();
        self::assertStringContainsString('00:00:01,000 --> 00:00:04,000', $output);
        self::assertStringContainsString('Salam world', $output);
    }

    public function test_vtt_round_trip_preserves_header(): void
    {
        $driver = new VttDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.vtt')));

        $document->replaceText('Second cue', 'Cue two');

        $output = $document->toString();
        self::assertStringContainsString('WEBVTT', $output);
        self::assertStringContainsString('Cue two', $output);
    }
}
