<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Tests\Unit;

use Alirashidnahal\Polytext\Polytext;
use Alirashidnahal\Polytext\Tests\TestCase;

final class PolytextManagerTest extends TestCase
{
    public function test_it_opens_supported_text_files(): void
    {
        $polytext = new Polytext;
        $document = $polytext->open($this->fixturePath('sample.txt'));

        self::assertStringContainsString('Hello Polytext', $document->text());
        self::assertTrue($polytext->supports($this->fixturePath('sample.txt')));
    }

    public function test_it_registers_custom_drivers(): void
    {
        $polytext = new Polytext([]);
        $drivers = $polytext->drivers();

        self::assertCount(0, $drivers);
    }
}
