<?php

declare(strict_types=1);

return [
    /*
  |--------------------------------------------------------------------------
  | Additional Drivers
  |--------------------------------------------------------------------------
  |
  | Register custom driver classes here. The default driver set is loaded
  | automatically by the Polytext manager.
  |
  */
    'drivers' => [
        // App\Polytext\CustomDriver::class,
    ],

    /*
  |--------------------------------------------------------------------------
  | PDF Output Format
  |--------------------------------------------------------------------------
  |
  | Supported values: pdf, docx
  |
  */
    'pdf_output_format' => env('POLYTEXT_PDF_OUTPUT_FORMAT', 'pdf'),
];
