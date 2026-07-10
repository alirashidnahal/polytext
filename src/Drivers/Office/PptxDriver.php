<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class PptxDriver extends AbstractPresentationDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'pptx',
            extensions: ['pptx'],
            mimeTypes: ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        );
    }

    protected function readerType(): string
    {
        return 'PowerPoint2007';
    }

    protected function writerType(): string
    {
        return 'PowerPoint2007';
    }
}
