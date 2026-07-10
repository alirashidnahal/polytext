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
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

abstract class AbstractPhpWordDriver extends AbstractDriver
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
        $this->ensureClassExists(PhpWord::class, 'phpoffice/phpword');

        $path = $source->createTempPath();

        try {
            $phpWord = IOFactory::load($path, $this->readerType());
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to read Word document: '.$throwable->getMessage(), previous: $throwable);
        }

        $nodes = new NodeCollection;
        $textMap = [];
        $index = 0;

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $this->collectElements($element, $nodes, $textMap, $index);
            }
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromPath($path),
            metadata: ['format' => $this->name()],
            state: ['phpword' => $phpWord, 'text_map' => $textMap],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $this->ensureClassExists(PhpWord::class, 'phpoffice/phpword');

        /** @var array<string, Text> $textMap */
        $textMap = $document->state()['text_map'] ?? [];

        foreach ($document->nodes() as $node) {
            $element = $textMap[$node->id()] ?? null;

            if ($element instanceof Text) {
                $element->setText($node->text());
            }
        }

        /** @var PhpWord $phpWord */
        $phpWord = $document->state()['phpword'];
        $tempPath = tempnam(sys_get_temp_dir(), 'polytext_word_');

        if ($tempPath === false) {
            throw new CorruptFileException('Unable to create temporary Word file.');
        }

        $outputPath = $tempPath.'.'.$this->extensions[0];
        rename($tempPath, $outputPath);

        try {
            IOFactory::createWriter($phpWord, $this->writerType())->save($outputPath);
            $target->write((string) file_get_contents($outputPath));
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to write Word document: '.$throwable->getMessage(), previous: $throwable);
        } finally {
            if (is_file($outputPath)) {
                unlink($outputPath);
            }
        }
    }

    /**
     * @param  array<string, Text>  $textMap
     */
    private function collectElements(AbstractElement $element, NodeCollection $nodes, array &$textMap, int &$index): void
    {
        if ($element instanceof Text) {
            $text = trim($element->getText() ?? '');

            if ($text === '') {
                return;
            }

            $id = "word:{$index}";
            $nodes->add(new TextNode($id, $text, ['index' => $index]));
            $textMap[$id] = $element;
            $index++;

            return;
        }

        if ($element instanceof TextRun) {
            foreach ($element->getElements() as $child) {
                $this->collectElements($child, $nodes, $textMap, $index);
            }
        }
    }
}
