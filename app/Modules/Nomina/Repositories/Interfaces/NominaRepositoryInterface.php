<?php

namespace App\Modules\Nomina\Repositories\Interfaces;

use App\Modules\Nomina\Models\Nomina;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface NominaRepositoryInterface
{
    /**
     * Obtener todas las nóminas
     */
    public function all(): Collection;

    /**
     * Obtener nóminas con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Encontrar nómina por ID
     */
    public function find(int $id): ?Nomina;

    /**
     * Encontrar nómina por ID o fallar
     */
    public function findOrFail(int $id): Nomina;

    /**
     * Crear nueva nómina
     */
    public function create(array $data): Nomina;

    /**
     * Actualizar nómina
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar nómina
     */
    public function delete(int $id): bool;

    /**
     * Obtener nóminas por estado
     */
    public function porEstado(string $estado): Collection;

    /**
     * Obtener nóminas en borrador
     */
    public function getBorradores(): Collection;

    /**
     * Obtener nóminas preliquidadas
     */
    public function getPreliquidadas(): Collection;

    /**
     * Obtener nóminas aprobadas
     */
    public function getAprobadas(): Collection;

    /**
     * Obtener nóminas pagadas
     */
    public function getPagadas(): Collection;

    /**
     * Obtener nóminas por período
     */
    public function porPeriodo(int $periodoId): Collection;

    /**
     * Obtener nóminas por tipo
     */
    public function porTipo(int $tipoId): Collection;

    /**
     * Obtener nóminas por rango de fechas
     */
    public function porRangoFechas(string $fechaInicio, string $fechaFin): Collection;

    /**
     * Obtener nóminas del mes
     */
    public function delMes(int $mes, int $anio): Collection;

    /**
     * Obtener nóminas del año
     */
    public function delAnio(int $anio): Collection;

    /**
     * Obtener última nómina
     */
    public function getUltima(): ?Nomina;

    /**
     * Obtener última nómina por tipo
     */
    public function getUltimaPorTipo(int $tipoId): ?Nomina;

    /**
     * Obtener nómina actual (del mes actual)
     */
    public function getActual(): ?Nomina;

    /**
     * Verificar si existe nómina en período
     */
    public function existeEnPeriodo(int $periodoId, ?int $exceptoId = null): bool;

    /**
     * Aprobar nómina
     */
    public function aprobar(int $id): bool;

    /**
     * Marcar como pagada
     */
    public function marcarComoPagada(int $id, string $fechaPago): bool;

    /**
     * Cerrar nómina
     */
    public function cerrar(int $id): bool;

    /**
     * Anular nómina
     */
    public function anular(int $id, string $motivoAnulacion): bool;

    /**
     * Obtener total devengado de una nómina
     */
    public function getTotalDevengado(int $id): float;

    /**
     * Obtener total deducciones de una nómina
     */
    public function getTotalDeducciones(int $id): float;

    /**
     * Obtener total neto de una nómina
     */
    public function getTotalNeto(int $id): float;

    /**
     * Obtener número de empleados de una nómina
     */
    public function getNumeroEmpleados(int $id): int;

    /**
     * Obtener resumen mensual
     */
    public function getResumenMensual(int $mes, int $anio): array;

    /**
     * Obtener resumen anual
     */
    public function getResumenAnual(int $anio): array;

    /**
     * Generar número de nómina
     */
    public function generarNumeroNomina(int $tipoId, int $periodoId): string;
}