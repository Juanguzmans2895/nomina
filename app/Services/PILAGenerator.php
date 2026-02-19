<?php

namespace App\Services;

use App\Modules\Nomina\Models\Nomina;
use Carbon\Carbon;

class PILAGenerator
{
    protected $nomina;
    protected $tipoArchivo = 2; // 1 = Pruebas, 2 = Producción

    public function __construct(Nomina $nomina)
    {
        $this->nomina = $nomina;
    }

    /**
     * Generar archivo PILA completo
     */
    public function generate(): string
    {
        $contenido = '';
        
        // Tipo 1: Encabezado
        $contenido .= $this->generarEncabezado();
        
        // Tipo 2: Datos del aportante
        $contenido .= $this->generarDatosAportante();
        
        // Tipo 3-5: Cotizantes (empleados)
        foreach ($this->nomina->detallesNomina as $detalle) {
            $contenido .= $this->generarCotizante($detalle);
        }
        
        return $contenido;
    }

    /**
     * Tipo 1: Registro de encabezado
     */
    protected function generarEncabezado(): string
    {
        $linea = '';
        
        // 1. Tipo de registro (2)
        $linea .= str_pad('1', 2, '0', STR_PAD_LEFT);
        
        // 2. Modalidad de planilla (Y = Aporte, N = Corrección)
        $linea .= 'Y';
        
        // 3. Secuencia (1)
        $linea .= str_pad('1', 5, '0', STR_PAD_LEFT);
        
        // 4. Nombre del archivo
        $nombreArchivo = 'PILA_' . $this->nomina->numero_nomina . '_' . now()->format('YmdHis');
        $linea .= str_pad(substr($nombreArchivo, 0, 50), 50, ' ');
        
        // 5. Fecha y hora de generación (YYYYMMDDHHMMSS)
        $linea .= now()->format('YmdHis');
        
        // 6. Tipo de aporte (E = Empleados, I = Independientes)
        $linea .= 'E';
        
        // 7. Número de registros tipo 2 (siempre 1)
        $linea .= str_pad('1', 8, '0', STR_PAD_LEFT);
        
        // 8. Número de registros tipo 3-5 (empleados)
        $linea .= str_pad($this->nomina->numero_empleados, 8, '0', STR_PAD_LEFT);
        
        // 9. Versión del formato (3 caracteres)
        $linea .= '007';
        
        return $linea . "\r\n";
    }

    /**
     * Tipo 2: Datos del aportante (empresa)
     */
    protected function generarDatosAportante(): string
    {
        $linea = '';
        
        // 1. Tipo de registro
        $linea .= str_pad('2', 2, '0', STR_PAD_LEFT);
        
        // 2. Tipo de identificación (NI = NIT)
        $linea .= 'NI';
        
        // 3. Número de identificación (sin dígito de verificación)
        $nit = preg_replace('/[^0-9]/', '', config('nomina.empresa.nit', '900000000'));
        $linea .= str_pad(substr($nit, 0, 16), 16, '0', STR_PAD_LEFT);
        
        // 4. Dígito de verificación
        $linea .= str_pad($this->calcularDigitoVerificacion($nit), 1, '0', STR_PAD_LEFT);
        
        // 5. Razón social
        $razonSocial = config('app.name', 'MI EMPRESA SAS');
        $linea .= str_pad(substr($razonSocial, 0, 200), 200, ' ');
        
        // 6. Tipo de aportante (01 = Empleador)
        $linea .= '01';
        
        // 7. Dirección
        $direccion = config('nomina.empresa.direccion', 'CALLE 123 45 67');
        $linea .= str_pad(substr($direccion, 0, 40), 40, ' ');
        
        // 8. Código ciudad/municipio (11001 = Bogotá)
        $linea .= '11001';
        
        // 9. Teléfono
        $telefono = preg_replace('/[^0-9]/', '', config('nomina.empresa.telefono', '3001234567'));
        $linea .= str_pad(substr($telefono, 0, 10), 10, '0', STR_PAD_LEFT);
        
        // 10. Período de pago (YYYYMM)
        $linea .= $this->nomina->fecha_inicio->format('Ym');
        
        // 11. Fecha de pago (YYYYMMDD)
        $linea .= $this->nomina->fecha_pago->format('Ymd');
        
        // 12. Número total de empleados
        $linea .= str_pad($this->nomina->numero_empleados, 5, '0', STR_PAD_LEFT);
        
        // 13. Número de afiliados cotizantes
        $linea .= str_pad($this->nomina->numero_empleados, 5, '0', STR_PAD_LEFT);
        
        // 14. Tipo de planilla (E = Empleados)
        $linea .= 'E';
        
        // 15. Pago extemporáneo (N)
        $linea .= 'N';
        
        return $linea . "\r\n";
    }

    /**
     * Tipo 3-5: Cotizante (empleado)
     */
    protected function generarCotizante($detalle): string
    {
        $linea = '';
        $empleado = $detalle->empleado;
        
        // 1. Tipo de registro
        $linea .= str_pad('3', 2, '0', STR_PAD_LEFT);
        
        // 2. Tipo de identificación
        $tipoDoc = $this->mapearTipoDocumento($empleado->tipo_documento);
        $linea .= $tipoDoc;
        
        // 3. Número de identificación
        $documento = preg_replace('/[^0-9]/', '', $empleado->numero_documento);
        $linea .= str_pad(substr($documento, 0, 16), 16, '0', STR_PAD_LEFT);
        
        // 4. Primer apellido
        $linea .= str_pad(substr($empleado->primer_apellido, 0, 20), 20, ' ');
        
        // 5. Segundo apellido
        $linea .= str_pad(substr($empleado->segundo_apellido ?? '', 0, 30), 30, ' ');
        
        // 6. Primer nombre
        $linea .= str_pad(substr($empleado->primer_nombre, 0, 20), 20, ' ');
        
        // 7. Segundo nombre
        $linea .= str_pad(substr($empleado->segundo_nombre ?? '', 0, 30), 30, ' ');
        
        // 8. Ingreso (I) o Retiro (R) o Normal (X)
        $linea .= 'X';
        
        // 9. Tipo de salario (1 = Integral, 0 = Normal)
        $linea .= '0';
        
        // 10. Salario básico (sin decimales)
        $linea .= str_pad(round($detalle->salario_basico), 9, '0', STR_PAD_LEFT);
        
        // 11. IBC Salud
        $linea .= str_pad(round($detalle->salario_basico), 9, '0', STR_PAD_LEFT);
        
        // 12. IBC Pensión
        $linea .= str_pad(round($detalle->salario_basico), 9, '0', STR_PAD_LEFT);
        
        // 13. IBC ARL
        $linea .= str_pad(round($detalle->salario_basico), 9, '0', STR_PAD_LEFT);
        
        // 14. IBC CCF (Caja de Compensación)
        $linea .= str_pad(round($detalle->salario_basico), 9, '0', STR_PAD_LEFT);
        
        // 15. Días cotizados
        $linea .= str_pad('30', 2, '0', STR_PAD_LEFT);
        
        // 16. Código EPS (por defecto EPS001 - ajustar según corresponda)
        $linea .= 'EPS001';
        
        // 17. Código Pensión (por defecto PEN001)
        $linea .= 'PEN001';
        
        // 18. Código ARL (por defecto ARL001)
        $linea .= 'ARL001';
        
        // 19. Código CCF (por defecto CCF001)
        $linea .= 'CCF001';
        
        // 20. Tarifa ARL (clase de riesgo)
        $claseRiesgo = $empleado->clase_riesgo ?? 0.00522;
        $linea .= str_pad(number_format($claseRiesgo * 100, 2, '', ''), 7, '0', STR_PAD_LEFT);
        
        return $linea . "\r\n";
    }

    /**
     * Calcular dígito de verificación NIT
     */
    protected function calcularDigitoVerificacion($nit): int
    {
        $vpri = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $suma = 0;
        $longitud = strlen($nit);
        
        for ($i = 0; $i < $longitud; $i++) {
            $suma += $nit[$longitud - 1 - $i] * $vpri[$i];
        }
        
        $resto = $suma % 11;
        
        if ($resto > 1) {
            return 11 - $resto;
        }
        
        return $resto;
    }

    /**
     * Mapear tipo de documento
     */
    protected function mapearTipoDocumento($tipo): string
    {
        $mapa = [
            'CC' => 'CC',
            'CE' => 'CE',
            'TI' => 'TI',
            'PA' => 'PA',
            'RC' => 'RC',
            'NIT' => 'NI',
        ];
        
        return $mapa[$tipo] ?? 'CC';
    }

    /**
     * Generar nombre de archivo
     */
    public function getFileName(): string
    {
        $nit = preg_replace('/[^0-9]/', '', config('nomina.empresa.nit', '900000000'));
        $periodo = $this->nomina->fecha_inicio->format('Ym');
        
        return "PILA_{$nit}_{$periodo}.txt";
    }
}