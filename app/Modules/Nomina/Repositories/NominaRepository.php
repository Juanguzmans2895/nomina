<?php

namespace App\Modules\Nomina\Repositories;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Repositories\Interfaces\NominaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class NominaRepository implements NominaRepositoryInterface
{
    protected $model;

    public function __construct(Nomina $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['tipo', 'periodo'])->orderBy('created_at', 'desc')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['tipo', 'periodo'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Nomina
    {
        return $this->model->with(['tipo', 'periodo', 'detalles'])->find($id);
    }

    public function findOrFail(int $id): Nomina
    {
        return $this->model->with(['tipo', 'periodo', 'detalles'])->findOrFail($id);
    }

    public function create(array $data): Nomina
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $nomina = $this->findOrFail($id);
        return $nomina->update($data);
    }

    public function delete(int $id): bool
    {
        $nomina = $this->findOrFail($id);
        return $nomina->delete();
    }

    public function porEstado(string $estado): Collection
    {
        return $this->model->where('estado', $estado)
            ->with(['tipo', 'periodo'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBorradores(): Collection
    {
        return $this->porEstado('borrador');
    }

    public function getPreliquidadas(): Collection
    {
        return $this->porEstado('preliquidada');
    }

    public function getAprobadas(): Collection
    {
        return $this->porEstado('aprobada');
    }

    public function getPagadas(): Collection
    {
        return $this->porEstado('pagada');
    }

    public function porPeriodo(int $periodoId): Collection
    {
        return $this->model->where('periodo_nomina_id', $periodoId)
            ->with(['tipo', 'detalles'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function porTipo(int $tipoId): Collection
    {
        return $this->model->where('tipo_nomina_id', $tipoId)
            ->with(['periodo', 'detalles'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function porRangoFechas(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->model->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
            ->with(['tipo', 'periodo'])
            ->orderBy('fecha_inicio')
            ->get();
    }

    public function delMes(int $mes, int $anio): Collection
    {
        return $this->model->whereYear('fecha_inicio', $anio)
            ->whereMonth('fecha_inicio', $mes)
            ->with(['tipo', 'periodo'])
            ->orderBy('fecha_inicio')
            ->get();
    }

    public function delAnio(int $anio): Collection
    {
        return $this->model->whereYear('fecha_inicio', $anio)
            ->with(['tipo', 'periodo'])
            ->orderBy('fecha_inicio')
            ->get();
    }

    public function getUltima(): ?Nomina
    {
        return $this->model->with(['tipo', 'periodo'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getUltimaPorTipo(int $tipoId): ?Nomina
    {
        return $this->model->where('tipo_nomina_id', $tipoId)
            ->with(['periodo', 'detalles'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getActual(): ?Nomina
    {
        $mes = Carbon::now()->month;
        $anio = Carbon::now()->year;
        
        return $this->model->whereYear('fecha_inicio', $anio)
            ->whereMonth('fecha_inicio', $mes)
            ->with(['tipo', 'periodo'])
            ->first();
    }

    public function existeEnPeriodo(int $periodoId, ?int $exceptoId = null): bool
    {
        $query = $this->model->where('periodo_nomina_id', $periodoId);
        
        if ($exceptoId) {
            $query->where('id', '!=', $exceptoId);
        }
        
        return $query->exists();
    }

    public function aprobar(int $id): bool
    {
        $nomina = $this->findOrFail($id);
        
        if ($nomina->estado !== 'preliquidada') {
            return false;
        }
        
        return $nomina->update([
            'estado' => 'aprobada',
            'fecha_aprobacion' => now(),
            'aprobado_por' => auth()->id(),
        ]);
    }

    public function marcarComoPagada(int $id, string $fechaPago): bool
    {
        $nomina = $this->findOrFail($id);
        
        if (!in_array($nomina->estado, ['aprobada', 'preliquidada'])) {
            return false;
        }
        
        return $nomina->update([
            'estado' => 'pagada',
            'fecha_pago' => $fechaPago,
        ]);
    }

    public function cerrar(int $id): bool
    {
        $nomina = $this->findOrFail($id);
        
        return $nomina->update([
            'estado' => 'cerrada',
            'fecha_cierre' => now(),
        ]);
    }

    public function anular(int $id, string $motivoAnulacion): bool
    {
        $nomina = $this->findOrFail($id);
        
        return $nomina->update([
            'estado' => 'anulada',
            'fecha_anulacion' => now(),
            'motivo_anulacion' => $motivoAnulacion,
            'anulado_por' => auth()->id(),
        ]);
    }

    public function getTotalDevengado(int $id): float
    {
        $nomina = $this->findOrFail($id);
        return $nomina->detalles->sum('total_devengado');
    }

    public function getTotalDeducciones(int $id): float
    {
        $nomina = $this->findOrFail($id);
        return $nomina->detalles->sum('total_deducciones');
    }

    public function getTotalNeto(int $id): float
    {
        $nomina = $this->findOrFail($id);
        return $nomina->detalles->sum('total_neto');
    }

    public function getNumeroEmpleados(int $id): int
    {
        $nomina = $this->findOrFail($id);
        return $nomina->detalles->count();
    }

    public function getResumenMensual(int $mes, int $anio): array
    {
        $nominas = $this->delMes($mes, $anio);
        
        return [
            'cantidad_nominas' => $nominas->count(),
            'total_devengado' => $nominas->sum('total_devengado'),
            'total_deducciones' => $nominas->sum('total_deducciones'),
            'total_neto' => $nominas->sum('total_neto'),
            'total_empleados' => $nominas->sum('numero_empleados'),
        ];
    }

    public function getResumenAnual(int $anio): array
    {
        $nominas = $this->delAnio($anio);
        
        $resumenPorMes = [];
        
        for ($mes = 1; $mes <= 12; $mes++) {
            $nominasMes = $nominas->filter(function($nomina) use ($mes) {
                return Carbon::parse($nomina->fecha_inicio)->month === $mes;
            });
            
            $resumenPorMes[$mes] = [
                'mes' => $mes,
                'cantidad' => $nominasMes->count(),
                'total_devengado' => $nominasMes->sum('total_devengado'),
                'total_deducciones' => $nominasMes->sum('total_deducciones'),
                'total_neto' => $nominasMes->sum('total_neto'),
            ];
        }
        
        return [
            'anio' => $anio,
            'cantidad_total_nominas' => $nominas->count(),
            'total_devengado' => $nominas->sum('total_devengado'),
            'total_deducciones' => $nominas->sum('total_deducciones'),
            'total_neto' => $nominas->sum('total_neto'),
            'por_mes' => $resumenPorMes,
        ];
    }

    public function generarNumeroNomina(int $tipoId, int $periodoId): string
    {
        $anio = Carbon::now()->year;
        $mes = Carbon::now()->format('m');
        
        $ultimaNomina = $this->model->where('tipo_nomina_id', $tipoId)
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', Carbon::now()->month)
            ->orderBy('numero_nomina', 'desc')
            ->first();
        
        $consecutivo = 1;
        
        if ($ultimaNomina && preg_match('/(\d+)$/', $ultimaNomina->numero_nomina, $matches)) {
            $consecutivo = intval($matches[1]) + 1;
        }
        
        return sprintf('NOM-%s%s-%04d', $anio, $mes, $consecutivo);
    }
}