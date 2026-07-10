<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class DocxDriver extends AbstractPhpWordDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'docx',
            extensions: ['docx'],
            mimeTypes: ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        );
    }

    protected function readerType(): string
    {
        return 'Word2007';
    }

    protected function writerType(): string
    {
        return 'Word2007';
    }
}
