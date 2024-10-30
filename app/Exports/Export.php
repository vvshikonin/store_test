<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use App\Exports\Contracts\withColumnDataTyping;
use App\Exports\Contracts\withColumnHozirontalAlignment;

abstract class Export extends DefaultValueBinder implements WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithCustomValueBinder
{
    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => [
                'font' => ['bold' => true]
            ],
        ];

        if ($this instanceof withColumnHozirontalAlignment)
            $styles = $this->handleHorizontalAlignment($styles,  $this->columnHorizontalAlignment());

        return $styles;
    }

    private function handleHorizontalAlignment(array $styles, array $columnHorizontalAlignment)
    {
        foreach ($columnHorizontalAlignment as $column => $alignment) {
            if (!isset($styles[$column]))
                $styles[$column] = [];

            if (!isset($styles[$column]['alignment']))
                $styles[$column]['alignment'] = [];

            if (!isset($styles[$column]['alignment']['horizontal']))
                $styles[$column]['alignment']['horizontal'] = $alignment;
        }

        return $styles;
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($this instanceof withColumnDataTyping)
            $columnDataTyping = $this->columnDataTyping();

        if (isset($columnDataTyping[$cell->getColumn()])) {
            $cell->setValueExplicit($value, $columnDataTyping[$cell->getColumn()]);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
