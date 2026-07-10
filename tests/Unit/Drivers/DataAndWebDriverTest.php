<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Tests\Unit\Drivers;

use Alirashidnahal\Polytext\Drivers\Data\CsvDriver;
use Alirashidnahal\Polytext\Drivers\Data\JsonDriver;
use Alirashidnahal\Polytext\Drivers\Data\YamlDriver;
use Alirashidnahal\Polytext\Drivers\Web\HtmlDriver;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Tests\TestCase;

final class DataAndWebDriverTest extends TestCase
{
    public function test_json_round_trip(): void
    {
        $driver = new JsonDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.json')));

        $document->replaceText('Hello', 'Salam');

        $output = $document->toString();
        self::assertStringContainsString('Salam', $output);
        self::assertStringContainsString('World', $output);
    }

    public function test_csv_round_trip(): void
    {
        $driver = new CsvDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.csv')));

        $document->replaceText('Hello', 'Salam');

        $output = $document->toString();
        self::assertStringContainsString('Salam', $output);
        self::assertStringContainsString('Bob', $output);
    }

    public function test_yaml_round_trip(): void
    {
        $driver = new YamlDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.yaml')));

        $document->replaceText('Hello', 'Salam');

        $output = $document->toString();
        self::assertStringContainsString('Salam', $output);
    }

    public function test_html_skips_script_content(): void
    {
        $driver = new HtmlDriver;
        $document = $driver->read(Source::fromPath($this->fixturePath('sample.html')));

        self::assertStringContainsString('Hello', $document->text());
        self::assertStringNotContainsString('alert', $document->text());
    }
}
