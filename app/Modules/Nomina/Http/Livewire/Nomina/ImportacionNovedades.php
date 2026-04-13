<?php

namespace App\Modules\Nomina\Http\Livewire\Nomina;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportacionNovedades extends Component
{
    use WithFileUploads;

    public $archivo;
    public $periodoNominaId;
    public $novedadesPreview = [];
    public $errores = [];
    public $estadisticas = [];
    public $paso = 1; // 1: Cargar archivo, 2: Validar, 3: Confirmar
    
    // Opciones
    public $sobreescribirExistentes = false;
    public $requiereAprobacion = true;
    
    protected $rules = [
        'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        'periodoNominaId' => 'required|exists:periodos_nomina,id',
    ];

    protected $messages = [
        'archivo.required' => 'Debe seleccionar un archivo',
        'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV',
        'archivo.max' => 'El archivo no debe superar 10MB',
        'periodoNominaId.required' => 'Debe seleccionar un período',
    ];

    public function render()
    {
        $periodos = PeriodoNomina::abiertos()
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->get();

        return view('nomina.livewire.nomina.importacion-novedades', [
            'periodos' => $periodos,
        ]);
    }

    public function procesarArchivo()
    {
        $this->validate();

        try {
            $this->resetValidation();
            $this->errores = [];
            $this->novedadesPreview = [];
            
            // Leer archivo Excel
            $datos = Excel::toArray([], $this->archivo->getRealPath())[0];
            
            // Validar encabezados
            if (!$this->validarEncabezados($datos[0])) {
                $this->errores[] = 'El archivo no tiene el formato correcto. Descargue la plantilla.';
                return;
            }

            // Procesar filas (omitir encabezado)
            $filas = array_slice($datos, 1);
            $fila_numero = 2; // Empezar en fila 2 (después del encabezado)

            foreach ($filas as $fila) {
                $resultado = $this->procesarFila($fila, $fila_numero);
                
                if ($resultado['valido']) {
                    $this->novedadesPreview[] = $resultado['datos'];
                } else {
                    $this->errores[] = "Fila {$fila_numero}: " . implode(', ', $resultado['errores']);
                }

                $fila_numero++;
            }

            // Calcular estadísticas
            $this->calcularEstadisticas();

            // Si no hay errores, avanzar al siguiente paso
            if (empty($this->errores)) {
                $this->paso = 2;
            } else {
                session()->flash('error', 'Se encontraron errores en el archivo. Por favor revíselos.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar archivo: ' . $e->getMessage());
        }
    }

    protected function validarEncabezados($encabezados)
    {
        $esperados = [
            'documento',
            'codigo_concepto',
            'fecha_novedad',
            'cantidad',
            'valor_unitario',
            'observaciones'
        ];

        $encabezados = array_map('strtolower', array_map('trim', $encabezados));
        
        foreach ($esperados as $esperado) {
            if (!in_array($esperado, $encabezados)) {
                return false;
            }
        }

        return true;
    }

    protected function procesarFila($fila, $numero_fila)
    {
        $errores = [];
        $datos = [];

        // Documento del empleado
        $documento = trim($fila[0] ?? '');
        if (empty($documento)) {
            $errores[] = 'Documento vacío';
        } else {
            $empleado = Empleado::where('numero_documento', $documento)->first();
            if (!$empleado) {
                $errores[] = "Empleado con documento {$documento} no encontrado";
            } else {
                $datos['empleado_id'] = $empleado->id;
                $datos['empleado_nombre'] = $empleado->nombre_completo;
                $datos['empleado_documento'] = $documento;
            }
        }

        // Código de concepto
        $codigoConcepto = trim($fila[1] ?? '');
        if (empty($codigoConcepto)) {
            $errores[] = 'Código de concepto vacío';
        } else {
            $concepto = ConceptoNomina::where('codigo', $codigoConcepto)->first();
            if (!$concepto) {
                $errores[] = "Concepto {$codigoConcepto} no encontrado";
            } elseif (!$concepto->activo) {
                $errores[] = "Concepto {$codigoConcepto} está inactivo";
            } else {
                    $datos['concepto_id'] = $concepto->id;
        $fechaNovedad = trim($fila[2] ?? '');
        if (empty($fechaNovedad)) {
            $errores[] = 'Fecha vacía';
        } else {
            try {
                $fecha = \Carbon\Carbon::parse($fechaNovedad);
                    $datos['fecha'] = $fecha->format('Y-m-d');
        }

        // Cantidad
        $cantidad = floatval($fila[3] ?? 0);
        $datos['cantidad'] = $cantidad;

        // Valor unitario
        $valorUnitario = floatval($fila[4] ?? 0);
        $datos['valor_unitario'] = $valorUnitario;

        // Calcular valor total
        $datos['valor_total'] = $cantidad * $valorUnitario;

        // Observaciones
        $datos['observaciones'] = trim($fila[5] ?? '');

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'datos' => $datos,
        ];
    }}}}
    
    protected function calcularEstadisticas()
    {
        $total = count($this->novedadesPreview);
        $devengados = collect($this->novedadesPreview)->where('clasificacion', 'devengado')->count();
        $deducidos = collect($this->novedadesPreview)->where('clasificacion', 'deducido')->count();
        $valorTotal = collect($this->novedadesPreview)->sum('valor_total');

        $this->estadisticas = [
            'total' => $total,
            'devengados' => $devengados,
            'deducidos' => $deducidos,
            'valor_total' => $valorTotal,
            'errores' => count($this->errores),
        ];
    }

    public function confirmarImportacion()
    {
        try {
            DB::beginTransaction();

            $importadas = 0;
            $omitidas = 0;

            foreach ($this->novedadesPreview as $novedad) {
                // Verificar si ya existe
                $existe = NovedadNomina::where('empleado_id', $novedad['empleado_id'])
                    ->where('concepto_id', $novedad['concepto_id'])
                    ->where('periodo_id', $this->periodoNominaId)
                    ->where('fecha', $novedad['fecha'])
                    ->exists();

                if ($existe && !$this->sobreescribirExistentes) {
                    $omitidas++;
                    continue;
                }

                if ($existe && $this->sobreescribirExistentes) {
                    NovedadNomina::where('empleado_id', $novedad['empleado_id'])
                        ->where('concepto_id', $novedad['concepto_id'])
                        ->where('periodo_id', $this->periodoNominaId)
                        ->where('fecha', $novedad['fecha'])
                        ->delete();
                }

                // Crear novedad
                NovedadNomina::create([
                    'empleado_id' => $novedad['empleado_id'],
                    'concepto_id' => $novedad['concepto_id'],
                    'periodo_id' => $this->periodoNominaId,
                    'fecha' => $novedad['fecha'],
                    'cantidad' => $novedad['cantidad'],
                    'valor_unitario' => $novedad['valor_unitario'],
                    'valor_total' => $novedad['valor_total'],
                    'observaciones' => $novedad['observaciones'],
                    'estado' => 'pendiente',
                    'procesada' => false,
                    'requiere_aprobacion' => $this->requiereAprobacion,
                    'created_by' => auth()->id(),
                ]);

                $importadas++;
            }

            DB::commit();

            session()->flash('success', "Importación completada: {$importadas} novedades importadas, {$omitidas} omitidas");
            
            $this->reset();
            $this->paso = 1;

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    public function descargarPlantilla()
    {
        $plantilla = [
            ['documento', 'codigo_concepto', 'fecha_novedad', 'cantidad', 'valor_unitario', 'observaciones'],
            ['1234567890', 'HE01', '2024-01-15', '8', '15000', 'Horas extras diurnas'],
            ['0987654321', 'RN01', '2024-01-20', '8', '18000', 'Recargo nocturno'],
        ];

        // Aquí iría la lógica para generar Excel
        // return Excel::download(new PlantillaNovedadesExport($plantilla), 'plantilla_novedades.xlsx');
        
        session()->flash('info', 'Descargando plantilla...');
    }

    public function cancelar()
    {
        $this->reset();
        $this->paso = 1;
    }

    public function volverPaso1()
    {
        $this->paso = 1;
        $this->novedadesPreview = [];
        $this->errores = [];
        $this->estadisticas = [];
    }
}