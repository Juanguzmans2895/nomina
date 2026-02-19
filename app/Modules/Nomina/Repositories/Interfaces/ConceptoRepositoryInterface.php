<?php

namespace App\Modules\Nomina\Repositories\Interfaces;

use App\Modules\Nomina\Models\ConceptoNomina;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ConceptoRepositoryInterface
{
    /**
     * Obtener todos los conceptos
     */
    public function all(): Collection;

    /**
     * Obtener todos los conceptos con paginación
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Encontrar concepto por ID
     */
    public function find(int $id): ?ConceptoNomina;

    /**
     * Encontrar concepto por ID o fallar
     */
    public function findOrFail(int $id): ConceptoNomina;

    /**
     * Crear nuevo concepto
     */
    public function create(array $data): ConceptoNomina;

    /**
     * Actualizar concepto
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar concepto
     */
    public function delete(int $id): bool;

    /**
     * Obtener conceptos activos
     */
    public function getActivos(): Collection;

    /**
     * Obtener conceptos inactivos
     */
    public function getInactivos(): Collection;

    /**
     * Filtrar por tipo (fijo, variable)
     */
    public function porTipo(string $tipo): Collection;

    /**
     * Filtrar por clasificación (devengado, deduccion)
     */
    public function porClasificacion(string $clasificacion): Collection;

    /**
     * Obtener devengados
     */
    public function getDevengados(): Collection;

    /**
     * Obtener deducciones
     */
    public function getDeducciones(): Collection;

    /**
     * Obtener conceptos fijos
     */
    public function getFijos(): Collection;

    /**
     * Obtener conceptos variables
     */
    public function getVariables(): Collection;

    /**
     * Buscar conceptos por término
     */
    public function buscar(string $termino): Collection;

    /**
     * Encontrar concepto por código
     */
    public function findByCodigo(string $codigo): ?ConceptoNomina;

    /**
     * Verificar si existe concepto con código
     */
    public function existeCodigo(string $codigo, ?int $exceptoId = null): bool;

    /**
     * Obtener conceptos ordenados por código
     */
    public function ordenadosPorCodigo(): Collection;

    /**
     * Obtener conceptos con cuenta contable
     */
    public function conCuentaContable(): Collection;

    /**
     * Obtener conceptos que afectan salario integral
     */
    public function queAfectanSalarioIntegral(): Collection;

    /**
     * Obtener conceptos que afectan base seguridad social
     */
    public function queAfectanBaseSeguridadSocial(): Collection;

    /**
     * Obtener conceptos para liquidación
     */
    public function paraLiquidacion(): Collection;

    /**
     * Activar concepto
     */
    public function activar(int $id): bool;

    /**
     * Inactivar concepto
     */
    public function inactivar(int $id): bool;
}