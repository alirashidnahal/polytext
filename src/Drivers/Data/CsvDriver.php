<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Data;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Drivers\Concerns\NormalizesEncoding;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;
use League\Csv\Reader;
use League\Csv\Writer;

final class CsvDriver extends AbstractDriver
{
    use NormalizesEncoding;

    public function __construct()
    {
        parent::__construct(
            driverName: 'csv',
            extensions: ['csv'],
            mimeTypes: ['text/csv', 'application/csv'],
        );
    }

    public function read(Source $source): Document
    {
        $this->ensureClassExists(Reader::class, 'league/csv');

        $content = $this->normalizeToUtf8($source->readContents());
        $reader = Reader::createFromString($content);
        $reader->setHeaderOffset(null);

        $nodes = new NodeCollection;
        $rows = [];
        $rowIndex = 0;

        foreach ($reader->getRecords() as $record) {
            $cells = array_values($record);
            $rows[] = $cells;

            foreach ($cells as $columnIndex => $cell) {
                if (! is_string($cell) || $cell === '') {
                    continue;
                }

                $nodes->add(new TextNode(
                    id: "cell:{$rowIndex}:{$columnIndex}",
                    text: $cell,
                    context: ['row' => $rowIndex, 'column' => $columnIndex],
                ));
            }

            $rowIndex++;
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromString($content, 'csv'),
            metadata: [
                'delimiter' => $reader->getDelimiter(),
                'enclosure' => $reader->getEnclosure(),
                'escape' => $reader->getEscape(),
            ],
            state: ['rows' => $rows],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $this->ensureClassExists(Writer::class, 'league/csv');

        /** @var list<list<string>> $rows */
        $rows = $document->state()['rows'] ?? [];

        foreach ($document->nodes() as $node) {
            $row = (int) $node->contextValue('row', -1);
            $column = (int) $node->contextValue('column', -1);

            if ($row >= 0 && $column >= 0 && isset($rows[$row][$column])) {
                $rows[$row][$column] = $node->text();
            }
        }

        $writer = Writer::createFromString();
        $writer->setDelimiter((string) ($document->metadata()['delimiter'] ?? ','));
        $writer->setEnclosure((string) ($document->metadata()['enclosure'] ?? '"'));
        $writer->setEscape((string) ($document->metadata()['escape'] ?? '\\'));
        $writer->insertAll($rows);

        $target->write($writer->toString());
    }
}
