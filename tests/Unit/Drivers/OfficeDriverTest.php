<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Tests\Unit\Drivers;

use Alirashidnahal\Polytext\Drivers\Office\DocxDriver;
use Alirashidnahal\Polytext\Drivers\Office\XlsxDriver;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

final class OfficeDriverTest extends TestCase
{
    public function test_docx_round_trip(): void
    {
        $path = $this->tempFilePath('docx');
        $phpWord = new PhpWord;
        $phpWord->addSection()->addText('Hello Office');
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        $driver = new DocxDriver;
        $document = $driver->read(Source::fromPath($path));
        $document->replaceText('Hello Office', 'Salam Office');

        $outputPath = $this->tempFilePath('docx');
        $document->save($outputPath);

        $roundTrip = $driver->read(Source::fromPath($outputPath));
        self::assertStringContainsString('Salam Office', $roundTrip->text());

        unlink($path);
        unlink($outputPath);
    }

    public function test_xlsx_round_trip(): void
    {
        $path = $this->tempFilePath('xlsx');
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Hello Sheet');
        (new XlsxWriter($spreadsheet))->save($path);

        $driver = new XlsxDriver;
        $document = $driver->read(Source::fromPath($path));
        $document->replaceText('Hello Sheet', 'Salam Sheet');

        $outputPath = $this->tempFilePath('xlsx');
        $document->save($outputPath);

        $roundTrip = $driver->read(Source::fromPath($outputPath));
        self::assertStringContainsString('Salam Sheet', $roundTrip->text());

        unlink($path);
        unlink($outputPath);
    }
}
