<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class PptDriver extends AbstractPresentationDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'ppt',
            extensions: ['ppt'],
            mimeTypes: ['application/vnd.ms-powerpoint'],
        );
    }

    protected function readerType(): string
    {
        return 'PowerPoint97';
    }

    protected function writerType(): string
    {
        return 'PowerPoint97';
    }
}
