<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Una hoja del reporte de comunidad. */
class ComunidadHoja extends DefaultValueBinder implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly string $titulo,
        private readonly array $encabezados,
        private readonly array $filas,
    ) {
    }

    public function title(): string
    {
        return mb_substr($this->titulo, 0, 31); // Excel limita el nombre de hoja a 31 caracteres
    }

    public function headings(): array
    {
        return $this->encabezados;
    }

    public function array(): array
    {
        return $this->filas;
    }

    public function styles(Worksheet $hoja): array
    {
        return [
            1                          => ['font' => ['bold' => true, 'size' => 13]],
            count($this->encabezados)  => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Los textos escritos por usuarios (títulos, nombres) se guardan siempre como
     * texto: así Excel nunca los interpreta como fórmula ("=", "+", "-", "@").
     */
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}