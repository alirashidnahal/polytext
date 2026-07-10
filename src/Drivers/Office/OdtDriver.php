<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class OdtDriver extends AbstractPhpWordDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'odt',
            extensions: ['odt'],
            mimeTypes: ['application/vnd.oasis.opendocument.text'],
        );
    }

    protected function readerType(): string
    {
        return 'ODText';
    }

    protected function writerType(): string
    {
        return 'ODText';
    }
}
