<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class PlanificacionExport extends DefaultValueBinder implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    public function __construct(private array $reporte) {}

    public function headings(): array
    {
        return [
            ['Cumplimiento de '.$this->reporte['tipo']],
            ['Desde', $this->reporte['desde'], 'Hasta', $this->reporte['hasta']],
            ['Cumplidos / registrados', $this->reporte['cumplidos'], 'Previstos', $this->reporte['esperados'], 'Cumplimiento (%)', $this->reporte['porcentaje']],
            [], $this->reporte['encabezados'],
        ];
    }

    public function array(): array
    {
        return $this->reporte['filas'];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
