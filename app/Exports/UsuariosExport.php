<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reporte de usuarios en Excel, filtrable por rol y estado.
 * No exporta peso/altura a propósito: son datos de salud cifrados (minimización de datos).
 */
class UsuariosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /** @param array{rol?: ?string, estado?: ?string, buscar?: ?string} $filtros */
    public function __construct(private readonly array $filtros = [])
    {
    }

    public function query(): Builder
    {
        return User::query()->with('perfil')->filtrar($this->filtros)->orderBy('id');
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Correo electrónico', 'Rol', 'Estado', 'Edad', 'Sexo', 'Fecha de registro'];
    }

    /** @param User $usuario */
    public function map($usuario): array
    {
        return [
            $usuario->id,
            $usuario->name,
            $usuario->email,
            $usuario->nombre_rol,
            $usuario->activo ? 'Activo' : 'Inactivo',
            $usuario->perfil?->edad,
            $usuario->perfil?->sexo ? ucfirst($usuario->perfil->sexo) : null,
            $usuario->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $hoja): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
