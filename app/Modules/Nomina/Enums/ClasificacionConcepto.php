<?php

namespace App\Modules\Nomina\Enums;

enum ClasificacionConcepto: string
{
    case DEVENGADO = 'devengado';
    case DEDUCIDO = 'deducido';
    case NO_IMPUTABLE = 'no_imputable';

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::DEVENGADO => 'Devengado',
            self::DEDUCIDO => 'Deducido',
            self::NO_IMPUTABLE => 'No Imputable',
        };
    }

    /**
     * Obtener el color
     */
    public function color(): string
    {
        return match($this) {
            self::DEVENGADO => 'green',
            self::DEDUCIDO => 'red',
            self::NO_IMPUTABLE => 'gray',
        };
    }

    /**
     * Verificar si es un ingreso
     */
    public function esIngreso(): bool
    {
        return $this === self::DEVENGADO;
    }

    /**
     * Verificar si es un egreso
     */
    public function esEgreso(): bool
    {
        return $this === self::DEDUCIDO;
    }

    /**
     * Obtener el signo para cálculos
     */
    public function signo(): int
    {
        return match($this) {
            self::DEVENGADO => 1,
            self::DEDUCIDO => -1,
            self::NO_IMPUTABLE => 0,
        };
    }
}
