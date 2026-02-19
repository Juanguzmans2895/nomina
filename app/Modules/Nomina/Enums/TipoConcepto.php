<?php

namespace App\Modules\Nomina\Enums;

enum TipoConcepto: string
{
    case FIJO = 'fijo';
    case NOVEDAD = 'novedad';
    case CALCULADO = 'calculado';

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::FIJO => 'Fijo',
            self::NOVEDAD => 'Novedad',
            self::CALCULADO => 'Calculado',
        };
    }

    /**
     * Obtener la descripción
     */
    public function descripcion(): string
    {
        return match($this) {
            self::FIJO => 'Se aplica automáticamente cada período',
            self::NOVEDAD => 'Debe registrarse manualmente o importarse',
            self::CALCULADO => 'Se calcula automáticamente por el sistema',
        };
    }

    /**
     * Verificar si se aplica automáticamente
     */
    public function esAutomatico(): bool
    {
        return in_array($this, [self::FIJO, self::CALCULADO]);
    }

    /**
     * Verificar si requiere registro manual
     */
    public function esManual(): bool
    {
        return $this === self::NOVEDAD;
    }
}
