<?php

namespace App\Modules\Nomina\Repositories;

use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Repositories\Interfaces\EmpleadoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class EmpleadoRepository implements EmpleadoRepositoryInterface
{
    protected $model;

    public function __construct(Empleado $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->paginate($perPage);
    }

    public function find(int $id): ?Empleado
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Empleado
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Empleado
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $empleado = $this->findOrFail($id);
        return $empleado->update($data);
    }

    public function delete(int $id): bool
    {
        $empleado = $this->findOrFail($id);
        return $empleado->delete();
    }

    public function getActivos(): Collection
    {
        return $this->model->where('estado', 'activo')->get();
    }

    public function getInactivos(): Collection
    {
        return $this->model->where('estado', 'inactivo')->get();
    }

    public function getRetirados(): Collection
    {
        return $this->model->where('estado', 'retirado')->get();
    }

    public function buscar(string $termino): Collection
    {
        return $this->model->where(function($query) use ($termino) {
            $query->where('primer_nombre', 'like', "%{$termino}%")
                  ->orWhere('segundo_nombre', 'like', "%{$termino}%")
                  ->orWhere('primer_apellido', 'like', "%{$termino}%")
                  ->orWhere('segundo_apellido', 'like', "%{$termino}%")
                  ->orWhere('numero_documento', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
        })->get();
    }

    public function porDependencia(string $dependencia): Collection
    {
        return $this->model->where('dependencia', $dependencia)->get();
    }

    public function porCargo(string $cargo): Collection
    {
        return $this->model->where('cargo', $cargo)->get();
    }

    public function porRangoSalarial(float $min, float $max): Collection
    {
        return $this->model->whereBetween('salario_basico', [$min, $max])->get();
    }

    public function findByDocumento(string $numeroDocumento): ?Empleado
    {
        return $this->model->where('numero_documento', $numeroDocumento)->first();
    }

    public function findByEmail(string $email): ?Empleado
    {
        return $this->model->where('email', $email)->first();
    }

    public function conCentrosCosto(): Collection
    {
        return $this->model->has('centrosCosto')->with('centrosCosto')->get();
    }

    public function conConceptosFijos(): Collection
    {
        return $this->model->has('conceptosFijos')->with('conceptosFijos')->get();
    }

    public function cumpleanosMes(int $mes): Collection
    {
        return $this->model->whereMonth('fecha_nacimiento', $mes)->get();
    }

    public function conAntiguedadMayorA(int $anios): Collection
    {
        $fecha = Carbon::now()->subYears($anios);
        return $this->model->where('fecha_ingreso', '<=', $fecha)->get();
    }

    public function contarPorEstado(): array
    {
        return $this->model->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
    }

    public function contarPorTipoContrato(): array
    {
        return $this->model->selectRaw('tipo_contrato, COUNT(*) as total')
            ->groupBy('tipo_contrato')
            ->pluck('total', 'tipo_contrato')
            ->toArray();
    }

    public function getTotalNomina(): float
    {
        return $this->model->where('estado', 'activo')->sum('salario_basico');
    }

    public function getPromedioSalarial(): float
    {
        return $this->model->where('estado', 'activo')->avg('salario_basico') ?? 0;
    }

    public function activar(int $id): bool
    {
        $empleado = $this->findOrFail($id);
        return $empleado->update([
            'estado' => 'activo',
            'fecha_retiro' => null,
            'motivo_retiro' => null,
        ]);
    }

    public function inactivar(int $id): bool
    {
        $empleado = $this->findOrFail($id);
        return $empleado->update(['estado' => 'inactivo']);
    }

    public function retirar(int $id, string $fechaRetiro, string $motivoRetiro): bool
    {
        $empleado = $this->findOrFail($id);
        return $empleado->update([
            'estado' => 'retirado',
            'fecha_retiro' => $fechaRetiro,
            'motivo_retiro' => $motivoRetiro,
        ]);
    }

    public function existeDocumento(string $numeroDocumento, ?int $exceptoId = null): bool
    {
        $query = $this->model->where('numero_documento', $numeroDocumento);
        
        if ($exceptoId) {
            $query->where('id', '!=', $exceptoId);
        }
        
        return $query->exists();
    }

    public function existeEmail(string $email, ?int $exceptoId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($exceptoId) {
            $query->where('id', '!=', $exceptoId);
        }
        
        return $query->exists();
    }
}