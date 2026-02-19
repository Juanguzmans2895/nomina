<?php

namespace App\Modules\Nomina\Enums;

enum TipoNomina: string
{
    case EMPLEADOS = 'empleados';
    case CONTRATISTAS = 'contratistas';
    case PRIMA = 'prima';
    case VACACIONES = 'vacaciones';
    case BONIFICACIONES = 'bonificaciones';
    case LIQUIDACION = 'liquidacion';

    /**
     * Obtener el label legible
     */
    public function label(): string
    {
        return match($this) {
            self::EMPLEADOS => 'Nómina General de Empleados',
            self::CONTRATISTAS => 'Nómina de Contratistas',
            self::PRIMA => 'Nómina de Prima',
            self::VACACIONES => 'Nómina de Vacaciones',
            self::BONIFICACIONES => 'Bonificaciones',
            self::LIQUIDACION => 'Liquidación de Contrato',
        };
    }

    /**
     * Obtener descripción corta
     */
    public function labelCorto(): string
    {
        return match($this) {
            self::EMPLEADOS => 'Empleados',
            self::CONTRATISTAS => 'Contratistas',
            self::PRIMA => 'Prima',
            self::VACACIONES => 'Vacaciones',
            self::BONIFICACIONES => 'Bonificaciones',
            self::LIQUIDACION => 'Liquidación',
        };
    }

    /**
     * Verificar si requiere seguridad social
     */
    public function requiereSeguridadSocial(): bool
    {
        return in_array($this, [
            self::EMPLEADOS,
            self::PRIMA,
            self::VACACIONES,
            self::LIQUIDACION
        ]);
    }

    /**
     * Verificar si requiere parafiscales
     */
    public function requiereParafiscales(): bool
    {
        return $this === self::EMPLEADOS;
    }

    /**
     * Verificar si es una nómina especial
     */
    public function esEspecial(): bool
    {
        return in_array($this, [
            self::PRIMA,
            self::VACACIONES,
            self::BONIFICACIONES,
            self::LIQUIDACION
        ]);
    }

    /**
     * Obtener el icono
     */
    public function icon(): string
    {
        return match($this) {
            self::EMPLEADOS => 'users',
            self::CONTRATISTAS => 'briefcase',
            self::PRIMA => 'gift',
            self::VACACIONES => 'sun',
            self::BONIFICACIONES => 'star',
            self::LIQUIDACION => 'document-text',
        };
    }

    /**
     * Obtener el color
     */
    public function color(): string
    {
        return match($this) {
            self::EMPLEADOS => 'blue',
            self::CONTRATISTAS => 'purple',
            self::PRIMA => 'green',
            self::VACACIONES => 'yellow',
            self::BONIFICACIONES => 'pink',
            self::LIQUIDACION => 'red',
        };
    }
}
