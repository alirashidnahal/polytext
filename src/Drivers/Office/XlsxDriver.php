<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Office;

final class XlsxDriver extends AbstractSpreadsheetDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'xlsx',
            extensions: ['xlsx'],
            mimeTypes: ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    protected function readerType(): string
    {
        return 'Xlsx';
    }

    protected function writerType(): string
    {
        return 'Xlsx';
    }
}
