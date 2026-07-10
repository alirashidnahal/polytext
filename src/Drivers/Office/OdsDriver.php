<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class OdsDriver extends AbstractSpreadsheetDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'ods',
            extensions: ['ods'],
            mimeTypes: ['application/vnd.oasis.opendocument.spreadsheet'],
        );
    }

    protected function readerType(): string
    {
        return 'Ods';
    }

    protected function writerType(): string
    {
        return 'Ods';
    }
}
