@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- ENCABEZADO -->
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h2">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Gestión de Nóminas
            </h1>
        </div>
        <div class="col-auto">
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCalcularNomina">
                <i class="bi bi-calculator"></i> Calcular Nueva Nómina
            </a>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Período</label>
                            <select name="periodo" class="form-select">
                                <option value="">-- Todos --</option>
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" {{ request('periodo') == $periodo->id ? 'selected' : '' }}>
                                        {{ $periodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">-- Todos --</option>
                                <option value="borrador" {{ request('estado') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                                <option value="prenomina" {{ request('estado') === 'prenomina' ? 'selected' : '' }}>Pre-nómina</option>
                                <option value="aprobada" {{ request('estado') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                                <option value="causada" {{ request('estado') === 'causada' ? 'selected' : '' }}>Causada</option>
                                <option value="contabilizada" {{ request('estado') === 'contabilizada' ? 'selected' : '' }}>Contabilizada</option>
                                <option value="pagada" {{ request('estado') === 'pagada' ? 'selected' : '' }}>Pagada</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE NÓMINAS -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N°</th>
                                <th>Período</th>
                                <th>Fechas</th>
                                <th>Empleados</th>
                                <th class="text-end">Total Devengado</th>
                                <th class="text-end">Total Deducciones</th>
                                <th class="text-end">Total Neto</th>
                                <th style="width: 120px;">Estado</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nominas as $nomina)
                            <tr>
                                <td><strong>{{ $nomina->numero_nomina }}</strong></td>
                                <td>{{ $nomina->periodo->nombre ?? 'N/A' }}</td>
                                <td>
                                    <small>
                                        {{ $nomina->fecha_inicio->format('d/m/Y') }} - 
                                        {{ $nomina->fecha_fin->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>{{ $nomina->numero_empleados ?? 0 }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format($nomina->total_devengado ?? 0, 2) }}</strong>
                                </td>
                                <td class="text-end text-danger">
                                    {{ number_format($nomina->total_deducciones ?? 0, 2) }}
                                </td>
                                <td class="text-end text-success">
                                    <strong>{{ number_format($nomina->total_neto ?? 0, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $nomina->estado === 'pagada' ? 'success' : ($nomina->estado === 'borrador' ? 'secondary' : ($nomina->estado === 'aprobada' ? 'info' : 'warning')) }}">
                                        {{ ucfirst($nomina->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('nomina.nominas.show', $nomina) }}" class="btn btn-outline-primary" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('nomina.nominas.exportarExcel', $nomina) }}" class="btn btn-outline-success" title="Descargar Excel">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay nóminas disponibles
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            @if($nominas->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $nominas->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL: CALCULAR NÓMINA -->
<div class="modal fade" id="modalCalcularNomina" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calculator"></i> Calcular Nueva Nómina
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('nomina.nominas.calcular') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="periodoSelect" class="form-label">Seleccionar Período</label>
                        <select name="periodo_id" id="periodoSelect" class="form-select" required>
                            <option value="">-- Selecciona un período --</option>
                            @foreach($periodos as $periodo)
                                <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Se calculará la nómina para todos los empleados activos del período seleccionado.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calculator"></i> Calcular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
