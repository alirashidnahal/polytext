<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Exceptions\CorruptFileException;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class AbstractSpreadsheetDriver extends AbstractDriver
{
    /**
     * @return non-empty-string
     */
    abstract protected function readerType(): string;

    /**
     * @return non-empty-string
     */
    abstract protected function writerType(): string;

    public function read(Source $source): Document
    {
        $this->ensureClassExists(Spreadsheet::class, 'phpoffice/phpspreadsheet');

        $path = $source->createTempPath();

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to read spreadsheet: '.$throwable->getMessage(), previous: $throwable);
        }

        $nodes = new NodeCollection;
        $cellMap = [];

        foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    if ($cell->isFormula()) {
                        continue;
                    }

                    $value = $cell->getValue();

                    if (! is_string($value) || $value === '') {
                        continue;
                    }

                    $coordinate = $cell->getCoordinate();
                    $id = "cell:{$sheetIndex}:{$coordinate}";
                    $nodes->add(new TextNode($id, $value, [
                        'sheet' => $sheetIndex,
                        'coordinate' => $coordinate,
                    ]));
                    $cellMap[$id] = $coordinate;
                }
            }
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromPath($path),
            metadata: ['format' => $this->name()],
            state: ['spreadsheet' => $spreadsheet, 'cell_map' => $cellMap],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $this->ensureClassExists(Spreadsheet::class, 'phpoffice/phpspreadsheet');

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $document->state()['spreadsheet'];
        /** @var array<string, string> $cellMap */
        $cellMap = $document->state()['cell_map'] ?? [];

        foreach ($document->nodes() as $node) {
            $coordinate = $cellMap[$node->id()] ?? null;

            if ($coordinate === null) {
                continue;
            }

            $sheetIndex = (int) $node->contextValue('sheet', 0);
            $spreadsheet->getSheet($sheetIndex)->setCellValue($coordinate, $node->text());
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'polytext_sheet_');

        if ($tempPath === false) {
            throw new CorruptFileException('Unable to create temporary spreadsheet file.');
        }

        $outputPath = $tempPath.'.'.$this->extensions[0];
        rename($tempPath, $outputPath);

        try {
            IOFactory::createWriter($spreadsheet, $this->writerType())->save($outputPath);
            $target->write((string) file_get_contents($outputPath));
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to write spreadsheet: '.$throwable->getMessage(), previous: $throwable);
        } finally {
            if (is_file($outputPath)) {
                unlink($outputPath);
            }
        }
    }
}
