<?php

namespace App\Modules\Nomina\Enums;

enum EstadoContrato: string
{
    case BORRADOR = 'borrador';
    case EN_TRAMITE = 'en_tramite';
    case APROBADO = 'aprobado';
    case ACTIVO = 'activo';
    case SUSPENDIDO = 'suspendido';
    case TERMINADO = 'terminado';
    case LIQUIDADO = 'liquidado';
    case ANULADO = 'anulado';

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::BORRADOR => 'Borrador',
            self::EN_TRAMITE => 'En Trámite',
            self::APROBADO => 'Aprobado',
            self::ACTIVO => 'Activo',
            self::SUSPENDIDO => 'Suspendido',
            self::TERMINADO => 'Terminado',
            self::LIQUIDADO => 'Liquidado',
            self::ANULADO => 'Anulado',
        };
    }

    /**
     * Obtener el color para badges
     */
    public function color(): string
    {
        return match($this) {
            self::BORRADOR => 'gray',
            self::EN_TRAMITE => 'blue',
            self::APROBADO => 'indigo',
            self::ACTIVO => 'green',
            self::SUSPENDIDO => 'yellow',
            self::TERMINADO => 'purple',
            self::LIQUIDADO => 'teal',
            self::ANULADO => 'red',
        };
    }

    /**
     * Obtener el icono
     */
    public function icon(): string
    {
        return match($this) {
            self::BORRADOR => 'document',
            self::EN_TRAMITE => 'clock',
            self::APROBADO => 'check-circle',
            self::ACTIVO => 'play',
            self::SUSPENDIDO => 'pause',
            self::TERMINADO => 'stop',
            self::LIQUIDADO => 'check-badge',
            self::ANULADO => 'x-circle',
        };
    }

    /**
     * Verificar si permite pagos
     */
    public function permitePagos(): bool
    {
        return in_array($this, [self::ACTIVO, self::SUSPENDIDO]);
    }

    /**
     * Verificar si permite modificaciones
     */
    public function permiteModificaciones(): bool
    {
        return in_array($this, [self::APROBADO, self::ACTIVO, self::SUSPENDIDO]);
    }

    /**
     * Verificar si está vigente
     */
    public function estaVigente(): bool
    {
        return in_array($this, [self::ACTIVO, self::SUSPENDIDO]);
    }
}