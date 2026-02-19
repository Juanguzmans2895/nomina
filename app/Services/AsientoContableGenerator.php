<?php

namespace App\Services;

use App\Modules\Nomina\Models\AsientoContableNomina;
use App\Modules\Nomina\Models\DetalleAsientoNomina;
use App\Modules\Nomina\Models\Nomina;

class AsientoContableGenerator
{
    /**
     * Generar asiento de causación de nómina
     */
    public function generarAsientoCausacion(Nomina $nomina): AsientoContableNomina
    {
        $nomina->load('detallesNomina.empleado');
        
        // Calcular totales
        $totalSalarios = 0;
        $totalSaludEmpleado = 0;
        $totalSaludEmpleador = 0;
        $totalPensionEmpleado = 0;
        $totalPensionEmpleador = 0;
        $totalARL = 0;
        $totalParafiscales = 0;
        
        foreach ($nomina->detallesNomina as $detalle) {
            $salario = $detalle->salario_basico;
            $totalSalarios += $salario;
            $totalSaludEmpleado += $salario * 0.04;
            $totalSaludEmpleador += $salario * 0.085;
            $totalPensionEmpleado += $salario * 0.04;
            $totalPensionEmpleador += $salario * 0.12;
            $totalARL += $salario * 0.00522;
            $totalParafiscales += $salario * 0.09; // 9% (SENA 2% + ICBF 3% + Caja 4%)
        }
        
        // Salario neto = Salario bruto - deducciones empleado
        $salarioNeto = $totalSalarios - $totalSaludEmpleado - $totalPensionEmpleado;
        
        // Total salud = empleado + empleador
        $totalSalud = $totalSaludEmpleado + $totalSaludEmpleador;
        
        // Total pensión = empleado + empleador
        $totalPension = $totalPensionEmpleado + $totalPensionEmpleador;
        
        // Total débitos (gastos)
        $totalDebitos = $totalSalarios + $totalSaludEmpleador + $totalPensionEmpleador + 
                       $totalARL + $totalParafiscales;
        
        // Total créditos (pasivos)
        $totalCreditos = $salarioNeto + $totalSalud + $totalPension + 
                        $totalARL + $totalParafiscales;
        
        // Generar número de asiento
        $numeroAsiento = $this->generarNumeroAsiento('causacion_nomina');
        
        // Crear asiento
        $asiento = AsientoContableNomina::create([
            'numero_asiento' => $numeroAsiento,
            'fecha_asiento' => $nomina->fecha_pago,
            'tipo_asiento' => 'causacion_nomina',
            'nomina_id' => $nomina->id,
            'descripcion' => "Causación nómina {$nomina->numero_nomina} - {$nomina->periodo->nombre}",
            'total_debitos' => $totalDebitos,
            'total_creditos' => $totalCreditos,
            'estado' => 'borrador',
        ]);
        
        // Crear detalles del asiento
        
        // === DÉBITOS (GASTOS) ===
        
        // 1. Gasto de Sueldos
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '510506',
            'nombre_cuenta' => 'Sueldos',
            'tercero' => 'Empleados',
            'debito' => $totalSalarios,
            'credito' => 0,
            'centro_costo' => 'Administración',
        ]);
        
        // 2. Gasto Salud Empleador
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '510524',
            'nombre_cuenta' => 'Aportes Salud Empleador',
            'tercero' => 'EPS',
            'debito' => $totalSaludEmpleador,
            'credito' => 0,
            'centro_costo' => 'Administración',
        ]);
        
        // 3. Gasto Pensión Empleador
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '510527',
            'nombre_cuenta' => 'Aportes Pensión Empleador',
            'tercero' => 'Fondo Pensión',
            'debito' => $totalPensionEmpleador,
            'credito' => 0,
            'centro_costo' => 'Administración',
        ]);
        
        // 4. Gasto ARL
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '510536',
            'nombre_cuenta' => 'Aportes ARL',
            'tercero' => 'ARL',
            'debito' => $totalARL,
            'credito' => 0,
            'centro_costo' => 'Administración',
        ]);
        
        // 5. Gasto Parafiscales
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '510568',
            'nombre_cuenta' => 'Aportes Parafiscales',
            'tercero' => 'SENA, ICBF, Caja',
            'debito' => $totalParafiscales,
            'credito' => 0,
            'centro_costo' => 'Administración',
        ]);
        
        // === CRÉDITOS (PASIVOS) ===
        
        // 6. Salarios por Pagar (neto)
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '250500',
            'nombre_cuenta' => 'Salarios por Pagar',
            'tercero' => 'Empleados',
            'debito' => 0,
            'credito' => $salarioNeto,
            'centro_costo' => 'Administración',
        ]);
        
        // 7. Salud por Pagar (total: empleado + empleador)
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '237005',
            'nombre_cuenta' => 'Aportes Salud por Pagar',
            'tercero' => 'EPS',
            'debito' => 0,
            'credito' => $totalSalud,
            'centro_costo' => 'Administración',
        ]);
        
        // 8. Pensión por Pagar (total: empleado + empleador)
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '237010',
            'nombre_cuenta' => 'Aportes Pensión por Pagar',
            'tercero' => 'Fondo Pensión',
            'debito' => 0,
            'credito' => $totalPension,
            'centro_costo' => 'Administración',
        ]);
        
        // 9. ARL por Pagar
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '237025',
            'nombre_cuenta' => 'ARL por Pagar',
            'tercero' => 'ARL',
            'debito' => 0,
            'credito' => $totalARL,
            'centro_costo' => 'Administración',
        ]);
        
        // 10. Parafiscales por Pagar
        DetalleAsientoNomina::create([
            'asiento_contable_id' => $asiento->id,
            'cuenta_contable' => '237035',
            'nombre_cuenta' => 'Parafiscales por Pagar',
            'tercero' => 'SENA, ICBF, Caja',
            'debito' => 0,
            'credito' => $totalParafiscales,
            'centro_costo' => 'Administración',
        ]);
        
        return $asiento;
    }
    
    /**
     * Generar número de asiento
     */
    private function generarNumeroAsiento(string $tipo): string
    {
        $anio = date('Y');
        $mes = date('m');
        
        $ultimoAsiento = AsientoContableNomina::where('tipo_asiento', $tipo)
            ->whereYear('fecha_asiento', $anio)
            ->whereMonth('fecha_asiento', $mes)
            ->orderBy('id', 'desc')
            ->first();
        
        $consecutivo = $ultimoAsiento ? 
            ((int) substr($ultimoAsiento->numero_asiento, -4)) + 1 : 1;
        
        $prefijo = [
            'causacion_nomina' => 'CN',
            'pago_nomina' => 'PN',
            'provision_mensual' => 'PR',
            'pago_provision' => 'PP',
        ];
        
        return ($prefijo[$tipo] ?? 'AS') . '-' . $anio . $mes . '-' . 
               str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }
}