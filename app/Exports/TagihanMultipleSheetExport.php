<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TagihanMultipleSheetExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new TagihanSheet($this->filters), // Sheet untuk data tagihan
            new ReturSheet($this->filters)      // Sheet untuk data retur
        ];
    }
}
