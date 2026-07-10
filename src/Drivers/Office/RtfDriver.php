<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class RtfDriver extends AbstractPhpWordDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'rtf',
            extensions: ['rtf'],
            mimeTypes: ['application/rtf', 'text/rtf'],
        );
    }

    protected function readerType(): string
    {
        return 'RTF';
    }

    protected function writerType(): string
    {
        return 'RTF';
    }
}
