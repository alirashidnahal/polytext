<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Pdf;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Exceptions\CorruptFileException;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;
use Dompdf\Dompdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Smalot\PdfParser\Parser;

final class PdfDriver extends AbstractDriver
{
    public function __construct(
        private readonly string $outputFormat = 'pdf',
    ) {
        parent::__construct(
            driverName: 'pdf',
            extensions: ['pdf'],
            mimeTypes: ['application/pdf'],
        );
    }

    public function read(Source $source): Document
    {
        $this->ensureClassExists(Parser::class, 'smalot/pdfparser');

        $path = $source->createTempPath();

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($path);
            $text = trim($pdf->getText());
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to read PDF document: '.$throwable->getMessage(), previous: $throwable);
        }

        if ($text === '') {
            throw new CorruptFileException('PDF appears to be image-only or contains no extractable text.');
        }

        $paragraphs = preg_split("/\R{2,}/", $text) ?: [$text];
        $nodes = new NodeCollection;

        foreach ($paragraphs as $index => $paragraph) {
            $nodes->add(new TextNode(
                id: "paragraph:{$index}",
                text: $paragraph,
                context: ['index' => $index],
            ));
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromPath($path),
            metadata: [
                'format' => 'pdf',
                'output_format' => $this->outputFormat,
                'page_count' => count($pdf->getPages()),
            ],
            state: ['source_path' => $path],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $outputFormat = (string) ($document->metadata()['output_format'] ?? $this->outputFormat);

        if ($outputFormat === 'docx') {
            $this->writeDocx($document, $target);

            return;
        }

        $this->writePdf($document, $target);
    }

    private function writePdf(Document $document, Target $target): void
    {
        $this->ensureClassExists(Dompdf::class, 'dompdf/dompdf');

        $html = '<html><body>';

        foreach ($document->nodes() as $node) {
            $html .= '<p>'.htmlspecialchars($node->text(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p>';
        }

        $html .= '</body></html>';

        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->render();

        $target->write($dompdf->output());
    }

    private function writeDocx(Document $document, Target $target): void
    {
        $this->ensureClassExists(PhpWord::class, 'phpoffice/phpword');

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        foreach ($document->nodes() as $node) {
            $section->addText($node->text());
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'polytext_pdf_docx_');

        if ($tempPath === false) {
            throw new CorruptFileException('Unable to create temporary DOCX file.');
        }

        $outputPath = $tempPath.'.docx';
        rename($tempPath, $outputPath);

        try {
            IOFactory::createWriter($phpWord, 'Word2007')->save($outputPath);
            $target->write((string) file_get_contents($outputPath));
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Unable to write DOCX output: '.$throwable->getMessage(), previous: $throwable);
        } finally {
            if (is_file($outputPath)) {
                unlink($outputPath);
            }
        }
    }
}
