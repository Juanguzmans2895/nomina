<?php

namespace App\Modules\Nomina\Repositories\Interfaces;

use App\Modules\Nomina\Models\Empleado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EmpleadoRepositoryInterface
{
    /**
     * Obtener todos los empleados
     */
    public function all(): Collection;

    /**
     * Obtener todos los empleados con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Encontrar empleado por ID
     */
    public function find(int $id): ?Empleado;

    /**
     * Encontrar empleado por ID o fallar
     */
    public function findOrFail(int $id): Empleado;

    /**
     * Crear nuevo empleado
     */
    public function create(array $data): Empleado;

    /**
     * Actualizar empleado
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar empleado
     */
    public function delete(int $id): bool;

    /**
     * Obtener empleados activos
     */
    public function getActivos(): Collection;

    /**
     * Obtener empleados inactivos
     */
    public function getInactivos(): Collection;

    /**
     * Obtener empleados retirados
     */
    public function getRetirados(): Collection;

    /**
     * Buscar empleados por término
     */
    public function buscar(string $termino): Collection;

    /**
     * Filtrar empleados por dependencia
     */
    public function porDependencia(string $dependencia): Collection;

    /**
     * Filtrar empleados por cargo
     */
    public function porCargo(string $cargo): Collection;

    /**
     * Obtener empleados con salario en rango
     */
    public function porRangoSalarial(float $min, float $max): Collection;

    /**
     * Encontrar empleado por documento
     */
    public function findByDocumento(string $numeroDocumento): ?Empleado;

    /**
     * Encontrar empleado por email
     */
    public function findByEmail(string $email): ?Empleado;

    /**
     * Obtener empleados con centros de costo
     */
    public function conCentrosCosto(): Collection;

    /**
     * Obtener empleados con conceptos fijos
     */
    public function conConceptosFijos(): Collection;

    /**
     * Obtener empleados que cumplen años en el mes
     */
    public function cumpleanosMes(int $mes): Collection;

    /**
     * Obtener empleados con antigüedad mayor a
     */
    public function conAntiguedadMayorA(int $anios): Collection;

    /**
     * Contar empleados por estado
     */
    public function contarPorEstado(): array;

    /**
     * Contar empleados por tipo de contrato
     */
    public function contarPorTipoContrato(): array;

    /**
     * Obtener total de nómina (suma de salarios)
     */
    public function getTotalNomina(): float;

    /**
     * Obtener promedio de salarios
     */
    public function getPromedioSalarial(): float;

    /**
     * Activar empleado
     */
    public function activar(int $id): bool;

    /**
     * Inactivar empleado
     */
    public function inactivar(int $id): bool;

    /**
     * Retirar empleado
     */
    public function retirar(int $id, string $fechaRetiro, string $motivoRetiro): bool;

    /**
     * Verificar si existe empleado con documento
     */
    public function existeDocumento(string $numeroDocumento, ?int $exceptoId = null): bool;

    /**
     * Verificar si existe empleado con email
     */
    public function existeEmail(string $email, ?int $exceptoId = null): bool;
}