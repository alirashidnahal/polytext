<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext;

use Alirashidnahal\Polytext\Contracts\FormatDriver;
use Alirashidnahal\Polytext\Drivers\Data\CsvDriver;
use Alirashidnahal\Polytext\Drivers\Data\JsonDriver;
use Alirashidnahal\Polytext\Drivers\Data\YamlDriver;
use Alirashidnahal\Polytext\Drivers\Office\DocxDriver;
use Alirashidnahal\Polytext\Drivers\Office\OdsDriver;
use Alirashidnahal\Polytext\Drivers\Office\OdtDriver;
use Alirashidnahal\Polytext\Drivers\Office\PptDriver;
use Alirashidnahal\Polytext\Drivers\Office\PptxDriver;
use Alirashidnahal\Polytext\Drivers\Office\RtfDriver;
use Alirashidnahal\Polytext\Drivers\Office\XlsDriver;
use Alirashidnahal\Polytext\Drivers\Office\XlsxDriver;
use Alirashidnahal\Polytext\Drivers\Pdf\PdfDriver;
use Alirashidnahal\Polytext\Drivers\Subtitle\AssDriver;
use Alirashidnahal\Polytext\Drivers\Subtitle\SrtDriver;
use Alirashidnahal\Polytext\Drivers\Subtitle\SubDriver;
use Alirashidnahal\Polytext\Drivers\Subtitle\VttDriver;
use Alirashidnahal\Polytext\Drivers\Text\PlainTextDriver;
use Alirashidnahal\Polytext\Drivers\Web\HtmlDriver;
use Alirashidnahal\Polytext\Drivers\Web\XmlDriver;
use Alirashidnahal\Polytext\Exceptions\DriverNotFoundException;
use Alirashidnahal\Polytext\Exceptions\UnsupportedFormatException;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;

final class DriverRegistry
{
    /** @var array<string, FormatDriver> */
    private array $drivers = [];

    public function register(FormatDriver $driver): void
    {
        $this->drivers[$driver->name()] = $driver;
    }

    /**
     * @return list<FormatDriver>
     */
    public function all(): array
    {
        return array_values($this->drivers);
    }

    public function resolve(FileInfo $file): FormatDriver
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($file)) {
                return $driver;
            }
        }

        $extension = $file->extension ?? 'unknown';

        throw new DriverNotFoundException("No driver found for format: {$extension}");
    }

    public function supports(FileInfo $file): bool
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($file)) {
                return true;
            }
        }

        return false;
    }

    public function resolveByName(string $name): FormatDriver
    {
        if (! isset($this->drivers[$name])) {
            throw new DriverNotFoundException("Driver not registered: {$name}");
        }

        return $this->drivers[$name];
    }
}

final class Polytext
{
    private readonly DriverRegistry $registry;

    /**
     * @param  list<FormatDriver>|null  $drivers
     */
    public function __construct(?array $drivers = null)
    {
        $this->registry = new DriverRegistry;

        foreach ($drivers ?? self::defaultDrivers() as $driver) {
            $this->registry->register($driver);
        }
    }

    public function registerDriver(FormatDriver $driver): void
    {
        $this->registry->register($driver);
    }

    /**
     * @return list<FormatDriver>
     */
    public function drivers(): array
    {
        return $this->registry->all();
    }

    public function supports(string $path): bool
    {
        return $this->registry->supports(FileInfo::fromPath($path));
    }

    public function open(string|Source $source, ?string $driverName = null): Document\Document
    {
        $resolvedSource = is_string($source) ? Source::fromPath($source) : $source;
        $fileInfo = $resolvedSource->fileInfo ?? FileInfo::fromString($resolvedSource->readContents());

        $driver = $driverName !== null
            ? $this->registry->resolveByName($driverName)
            : $this->registry->resolve($fileInfo);

        if (! $driver->supports($fileInfo)) {
            throw new UnsupportedFormatException('The selected driver does not support this source.');
        }

        return $driver->read($resolvedSource);
    }

    /**
     * @return list<FormatDriver>
     */
    public static function defaultDrivers(): array
    {
        return [
            new PlainTextDriver,
            new SrtDriver,
            new VttDriver,
            new AssDriver,
            new SubDriver,
            new HtmlDriver,
            new XmlDriver,
            new JsonDriver,
            new CsvDriver,
            new YamlDriver,
            new DocxDriver,
            new OdtDriver,
            new RtfDriver,
            new XlsxDriver,
            new XlsDriver,
            new OdsDriver,
            new PptxDriver,
            new PptDriver,
            new PdfDriver,
        ];
    }
}
