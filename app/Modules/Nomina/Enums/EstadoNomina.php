<?php

namespace App\Modules\Nomina\Enums;

enum EstadoNomina: string
{
    case BORRADOR = 'borrador';
    case PRENOMINA = 'prenomina';
    case APROBADA = 'aprobada';
    case CAUSADA = 'causada';
    case CONTABILIZADA = 'contabilizada';
    case PAGADA = 'pagada';
    case ANULADA = 'anulada';

    /**
     * Obtener el label legible del estado
     */
    public function label(): string
    {
        return match($this) {
            self::BORRADOR => 'Borrador',
            self::PRENOMINA => 'Pre-nómina',
            self::APROBADA => 'Aprobada',
            self::CAUSADA => 'Causada',
            self::CONTABILIZADA => 'Contabilizada',
            self::PAGADA => 'Pagada',
            self::ANULADA => 'Anulada',
        };
    }

    /**
     * Obtener el color para badges
     */
    public function color(): string
    {
        return match($this) {
            self::BORRADOR => 'gray',
            self::PRENOMINA => 'blue',
            self::APROBADA => 'green',
            self::CAUSADA => 'indigo',
            self::CONTABILIZADA => 'purple',
            self::PAGADA => 'emerald',
            self::ANULADA => 'red',
        };
    }

    /**
     * Obtener el icono del estado
     */
    public function icon(): string
    {
        return match($this) {
            self::BORRADOR => 'document',
            self::PRENOMINA => 'clock',
            self::APROBADA => 'check-circle',
            self::CAUSADA => 'shield-check',
            self::CONTABILIZADA => 'calculator',
            self::PAGADA => 'currency-dollar',
            self::ANULADA => 'x-circle',
        };
    }

    /**
     * Verificar si el estado permite edición
     */
    public function puedeEditar(): bool
    {
        return in_array($this, [self::BORRADOR, self::PRENOMINA]);
    }

    /**
     * Verificar si el estado permite aprobación
     */
    public function puedeAprobar(): bool
    {
        return $this === self::PRENOMINA;
    }

    /**
     * Verificar si el estado permite causación
     */
    public function puedeCausar(): bool
    {
        return $this === self::APROBADA;
    }

    /**
     * Verificar si el estado permite contabilización
     */
    public function puedeContabilizar(): bool
    {
        return $this === self::CAUSADA;
    }

    /**
     * Verificar si el estado permite anulación
     */
    public function puedeAnular(): bool
    {
        return !in_array($this, [self::PAGADA, self::ANULADA]);
    }

    /**
     * Obtener el siguiente estado en el flujo
     */
    public function siguiente(): ?self
    {
        return match($this) {
            self::BORRADOR => self::PRENOMINA,
            self::PRENOMINA => self::APROBADA,
            self::APROBADA => self::CAUSADA,
            self::CAUSADA => self::CONTABILIZADA,
            self::CONTABILIZADA => self::PAGADA,
            default => null,
        };
    }

    /**
     * Obtener todos los estados en orden
     */
    public static function flujoOrdenado(): array
    {
        return [
            self::BORRADOR,
            self::PRENOMINA,
            self::APROBADA,
            self::CAUSADA,
            self::CONTABILIZADA,
            self::PAGADA,
        ];
    }
}
