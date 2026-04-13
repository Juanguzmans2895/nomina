@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- ENCABEZADO -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Liquidación de Nómina
            </h1>
            <p class="text-muted">{{ $nomina->nombre }} - {{ $nomina->periodo->nombre ?? 'N/A' }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('nomina.nominas.exportarExcel', $nomina) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                </a>
                <a href="{{ route('nomina.nominas.lista') }}" class="btn btn-secondary">
                    <i class="bi bi-list"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- RESUMEN DE TOTALES -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Devengado</h6>
                    <h4 class="mb-0">{{ number_format($totalDevengado, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Deducciones</h6>
                    <h4 class="mb-0 text-danger">-{{ number_format($totalDeducciones, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Neto a Pagar</h6>
                    <h4 class="mb-0 text-success">{{ number_format($totalNeto, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title text-muted">Aportes Empleador</h6>
                    <h4 class="mb-0 text-info">{{ number_format($totalAportesEmpleador, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DE LA NÓMINA -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Número:</dt>
                        <dd class="col-sm-8"><strong>{{ $nomina->numero_nomina }}</strong></dd>
                        
                        <dt class="col-sm-4">Período:</dt>
                        <dd class="col-sm-8">{{ $nomina->periodo->nombre ?? 'N/A' }}</dd>
                        
                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $nomina->estado === 'pagada' ? 'success' : ($nomina->estado === 'borrador' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($nomina->estado) }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Empleados:</dt>
                        <dd class="col-sm-8">{{ $nomina->numero_empleados ?? count($tabla) }}</dd>
                        
                        <dt class="col-sm-4">Fecha Inicio:</dt>
                        <dd class="col-sm-8">{{ $nomina->fecha_inicio->format('d/m/Y') }}</dd>
                        
                        <dt class="col-sm-4">Fecha Fin:</dt>
                        <dd class="col-sm-8">{{ $nomina->fecha_fin->format('d/m/Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Configuración de Cálculo</h5>
                </div>
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="inclSS" checked disabled>
                        <label class="form-check-label" for="inclSS">
                            ✓ Incluye Seguridad Social
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="inclPF" checked disabled>
                        <label class="form-check-label" for="inclPF">
                            ✓ Incluye Parafiscales
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="inclProv" checked disabled>
                        <label class="form-check-label" for="inclProv">
                            ✓ Incluye Provisiones
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA PRINCIPAL (ESTILO EXCEL) -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalle de Nómina - {{ count($tabla) }} Empleados</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" id="tablaNomina">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">Seq</th>
                                <th style="width: 8%">C.C</th>
                                <th style="width: 20%">EMPLEADOS</th>
                                <th style="width: 12%">CARGO</th>
                                <th style="width: 8%" class="text-end">SALARIO</th>
                                <th style="width: 6%" class="text-center">DIAS</th>
                                <th style="width: 8%" class="text-end">H.EXT D</th>
                                <th style="width: 8%" class="text-end">H.EXT N</th>
                                <th style="width: 8%" class="text-end">REC.NCT</th>
                                <th style="width: 8%" class="text-end">AUXILIO</th>
                                <th style="width: 8%" class="text-end">TOTAL DEV</th>
                                <th style="width: 8%" class="text-end">SALUD</th>
                                <th style="width: 8%" class="text-end">PENSION</th>
                                <th style="width: 8%" class="text-end">DESC.OT</th>
                                <th style="width: 8%" class="text-end">TOTAL DES</th>
                                <th style="width: 8%" class="text-end bg-success bg-opacity-25"><strong>NETO</strong></th>
                                <th style="width: 8%" class="text-end">CESANTIAS</th>
                                <th style="width: 8%" class="text-end">PRIMA</th>
                                <th style="width: 8%" class="text-end">VACACIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabla as $idx => $fila)
                            <tr>
                                <td class="text-muted">{{ $idx + 1 }}</td>
                                <td><strong>{{ $fila['empleado'] }}</strong></td>
                                <td>{{ $fila['nombre'] }}</td>
                                <td class="text-muted text-sm">{{ $fila['cargo'] }}</td>
                                <td class="text-end">{{ number_format($fila['salario_basico'], 2) }}</td>
                                <td class="text-center">{{ $fila['dias_trabajados'] }}</td>
                                <td class="text-end">{{ number_format($fila['horas_extras_diurnas'], 2) }}</td>
                                <td class="text-end">{{ number_format($fila['horas_extras_nocturnas'], 2) }}</td>
                                <td class="text-end">{{ number_format($fila['recargo_nocturno'], 2) }}</td>
                                <td class="text-end">{{ number_format($fila['auxilio_transporte'], 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($fila['total_devengado'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($fila['salud_empleado'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($fila['pension_empleado'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($fila['otros_descuentos'], 2) }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($fila['total_descuentos'], 2) }}</td>
                                <td class="text-end fw-bold bg-success bg-opacity-25 text-success">{{ number_format($fila['total_neto'], 2) }}</td>
                                <td class="text-end text-muted text-sm">{{ number_format($fila['cesantias'], 2) }}</td>
                                <td class="text-end text-muted text-sm">{{ number_format($fila['prima'], 2) }}</td>
                                <td class="text-end text-muted text-sm">{{ number_format($fila['vacaciones'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="19" class="text-center text-muted py-4">
                                    No hay empleados en esta nómina
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">TOTAL:</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-end">{{ number_format($totalDevengado, 2) }}</td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                                <td class="text-end text-danger">{{ number_format($totalDeducciones, 2) }}</td>
                                <td class="text-end bg-success bg-opacity-25 text-success">{{ number_format($totalNeto, 2) }}</td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- APORTES EMPLEADOR (para información) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumen de Aportes Empleador</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <p class="text-muted">Salud Empleador (8.5%)</p>
                            <h6>{{ number_format($tabla->sum('salud_empleador'), 2) }}</h6>
                        </div>
                        <div class="col-md-2">
                            <p class="text-muted">Pensión Empleador (12%)</p>
                            <h6>{{ number_format($tabla->sum('pension_empleador'), 2) }}</h6>
                        </div>
                        <div class="col-md-2">
                            <p class="text-muted">ARL (5.2%)</p>
                            <h6>{{ number_format($tabla->sum('arl'), 2) }}</h6>
                        </div>
                        <div class="col-md-2">
                            <p class="text-muted">CAJA (4%)</p>
                            <h6>{{ number_format($tabla->sum('caja_compensacion'), 2) }}</h6>
                        </div>
                        <div class="col-md-2">
                            <p class="text-muted">SENA (2%)</p>
                            <h6>{{ number_format($tabla->sum('sena'), 2) }}</h6>
                        </div>
                        <div class="col-md-2">
                            <p class="text-muted">ICBF (3%)</p>
                            <h6>{{ number_format($tabla->sum('icbf'), 2) }}</h6>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Total Aportes Empleador:</strong> {{ number_format($totalAportesEmpleador, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #tablaNomina {
        font-size: 0.85rem;
    }
    #tablaNomina thead th {
        font-weight: 600;
        white-space: nowrap;
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;
    }
    #tablaNomina tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
