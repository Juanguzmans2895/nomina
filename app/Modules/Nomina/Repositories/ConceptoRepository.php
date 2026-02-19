<?php

namespace App\Modules\Nomina\Repositories;

use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Repositories\Interfaces\ConceptoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ConceptoRepository implements ConceptoRepositoryInterface
{
    protected $model;

    public function __construct(ConceptoNomina $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('codigo')->paginate($perPage);
    }

    public function find(int $id): ?ConceptoNomina
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): ConceptoNomina
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): ConceptoNomina
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $concepto = $this->findOrFail($id);
        return $concepto->update($data);
    }

    public function delete(int $id): bool
    {
        $concepto = $this->findOrFail($id);
        return $concepto->delete();
    }

    public function getActivos(): Collection
    {
        return $this->model->where('activo', true)->orderBy('codigo')->get();
    }

    public function getInactivos(): Collection
    {
        return $this->model->where('activo', false)->orderBy('codigo')->get();
    }

    public function porTipo(string $tipo): Collection
    {
        return $this->model->where('tipo', $tipo)->orderBy('codigo')->get();
    }

    public function porClasificacion(string $clasificacion): Collection
    {
        return $this->model->where('clasificacion', $clasificacion)->orderBy('codigo')->get();
    }

    public function getDevengados(): Collection
    {
        return $this->porClasificacion('devengado');
    }

    public function getDeducciones(): Collection
    {
        return $this->porClasificacion('deduccion');
    }

    public function getFijos(): Collection
    {
        return $this->porTipo('fijo');
    }

    public function getVariables(): Collection
    {
        return $this->porTipo('variable');
    }

    public function buscar(string $termino): Collection
    {
        return $this->model->where(function($query) use ($termino) {
            $query->where('codigo', 'like', "%{$termino}%")
                  ->orWhere('nombre', 'like', "%{$termino}%")
                  ->orWhere('descripcion', 'like', "%{$termino}%");
        })->orderBy('codigo')->get();
    }

    public function findByCodigo(string $codigo): ?ConceptoNomina
    {
        return $this->model->where('codigo', $codigo)->first();
    }

    public function existeCodigo(string $codigo, ?int $exceptoId = null): bool
    {
        $query = $this->model->where('codigo', $codigo);
        
        if ($exceptoId) {
            $query->where('id', '!=', $exceptoId);
        }
        
        return $query->exists();
    }

    public function ordenadosPorCodigo(): Collection
    {
        return $this->model->orderBy('codigo')->get();
    }

    public function conCuentaContable(): Collection
    {
        return $this->model->whereNotNull('cuenta_contable_id')
            ->with('cuentaContable')
            ->orderBy('codigo')
            ->get();
    }

    public function queAfectanSalarioIntegral(): Collection
    {
        return $this->model->where('afecta_salario_integral', true)
            ->orderBy('codigo')
            ->get();
    }

    public function queAfectanBaseSeguridadSocial(): Collection
    {
        return $this->model->where('base_seguridad_social', true)
            ->orderBy('codigo')
            ->get();
    }

    public function paraLiquidacion(): Collection
    {
        return $this->model->where('activo', true)
            ->orderBy('orden')
            ->orderBy('codigo')
            ->get();
    }

    public function activar(int $id): bool
    {
        $concepto = $this->findOrFail($id);
        return $concepto->update(['activo' => true]);
    }

    public function inactivar(int $id): bool
    {
        $concepto = $this->findOrFail($id);
        return $concepto->update(['activo' => false]);
    }
}