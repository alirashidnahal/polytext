<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class XlsDriver extends AbstractSpreadsheetDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'xls',
            extensions: ['xls'],
            mimeTypes: ['application/vnd.ms-excel'],
        );
    }

    protected function readerType(): string
    {
        return 'Xls';
    }

    protected function writerType(): string
    {
        return 'Xls';
    }
}
