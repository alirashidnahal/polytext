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
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\RichText;

abstract class AbstractPresentationDriver extends AbstractDriver
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
        $this->ensureClassExists(PhpPresentation::class, 'phpoffice/phppresentation');

        $path = $source->createTempPath();

        try {
            $presentation = IOFactory::load($path);
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to read presentation: '.$throwable->getMessage(), previous: $throwable);
        }

        $nodes = new NodeCollection;
        $shapeMap = [];

        foreach ($presentation->getAllSlides() as $slideIndex => $slide) {
            foreach ($slide->getShapeCollection() as $shapeIndex => $shape) {
                if (! $shape instanceof RichText) {
                    continue;
                }

                $paragraphs = [];

                foreach ($shape->getParagraphs() as $paragraph) {
                    $paragraphs[] = $paragraph->getPlainText();
                }

                $text = trim(implode("\n", array_filter($paragraphs)));

                if ($text === '') {
                    continue;
                }

                $id = "slide:{$slideIndex}:{$shapeIndex}";
                $nodes->add(new TextNode($id, $text, [
                    'slide' => $slideIndex,
                    'shape' => $shapeIndex,
                ]));
                $shapeMap[$id] = $shape;
            }
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromPath($path),
            metadata: ['format' => $this->name()],
            state: ['presentation' => $presentation, 'shape_map' => $shapeMap],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $this->ensureClassExists(PhpPresentation::class, 'phpoffice/phppresentation');

        /** @var array<string, RichText> $shapeMap */
        $shapeMap = $document->state()['shape_map'] ?? [];

        foreach ($document->nodes() as $node) {
            $shape = $shapeMap[$node->id()] ?? null;

            if ($shape === null) {
                continue;
            }

            $paragraphs = $shape->getParagraphs();

            if (isset($paragraphs[0])) {
                $paragraphs[0]->getRichTextElements()[0]->setText($node->text());
            }
        }

        /** @var PhpPresentation $presentation */
        $presentation = $document->state()['presentation'];
        $tempPath = tempnam(sys_get_temp_dir(), 'polytext_ppt_');

        if ($tempPath === false) {
            throw new CorruptFileException('Unable to create temporary presentation file.');
        }

        $outputPath = $tempPath.'.'.$this->extensions[0];
        rename($tempPath, $outputPath);

        try {
            IOFactory::createWriter($presentation, $this->writerType())->save($outputPath);
            $target->write((string) file_get_contents($outputPath));
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to write presentation: '.$throwable->getMessage(), previous: $throwable);
        } finally {
            if (is_file($outputPath)) {
                unlink($outputPath);
            }
        }
    }
}
