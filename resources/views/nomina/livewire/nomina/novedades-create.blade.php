@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Crear Novedad</h1>
                    <p class="text-gray-600 mt-1">Registrar incapacidad, hora extra, descuento u otra novedad</p>
                </div>
                <a href="{{ route('nomina.novedades.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errores en el formulario:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Formulario --}}
        <form action="{{ route('nomina.novedades.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Información de la Novedad --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Información de la Novedad</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Empleado --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Empleado <span class="text-red-500">*</span>
                        </label>
                        <select name="empleado_id" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccione un empleado...</option>
                            @foreach($empleados ?? [] as $empleado)
                                <option value="{{ $empleado->id }}" {{ old('empleado_id') == $empleado->id ? 'selected' : '' }}>
                                    {{ $empleado->nombre_completo }} - {{ $empleado->numero_documento }}
                                </option>
                            @endforeach
                        </select>
                        @error('empleado_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Concepto --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Concepto <span class="text-red-500">*</span>
                        </label>
                        <select name="concepto_nomina_id" required id="concepto_select"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccione un concepto...</option>
                            @foreach($conceptos ?? [] as $concepto)
                                <option value="{{ $concepto->id }}" 
                                    data-tipo-calculo="{{ $concepto->tipo_calculo }}"
                                    data-valor-defecto="{{ $concepto->valor_defecto }}"
                                    {{ old('concepto_nomina_id') == $concepto->id ? 'selected' : '' }}>
                                    {{ $concepto->nombre }} ({{ ucfirst($concepto->tipo) }})
                                </option>
                            @endforeach
                        </select>
                        @error('concepto_nomina_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Seleccione el tipo de novedad (hora extra, incapacidad, descuento, etc.)</p>
                    </div>

                    {{-- Fecha --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('fecha')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Período --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                        <select name="periodo_nomina_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Sin asignar</option>
                            @foreach($periodos ?? [] as $periodo)
                                <option value="{{ $periodo->id }}" {{ old('periodo_nomina_id') == $periodo->id ? 'selected' : '' }}>
                                    {{ $periodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Opcional: Asignar a un período específico</p>
                    </div>

                    {{-- Cantidad --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" required step="0.01" min="0"
                            id="cantidad_input"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('cantidad')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Horas, días, unidades, etc.</p>
                    </div>

                    {{-- Valor Unitario --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Valor Unitario <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="valor_unitario" value="{{ old('valor_unitario', 0) }}" required step="0.01" min="0"
                            id="valor_unitario_input"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('valor_unitario')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Valor por hora, día, unidad, etc.</p>
                    </div>

                    {{-- Valor Total (calculado) --}}
                    <div class="md:col-span-2 bg-blue-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total</label>
                        <div class="text-2xl font-bold text-blue-600" id="valor_total_display">
                            $0
                        </div>
                        <p class="mt-1 text-xs text-gray-600">Cantidad × Valor Unitario</p>
                    </div>

                    {{-- Observaciones --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea name="observaciones" rows="3" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Detalles adicionales sobre la novedad...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Procesada --}}
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="procesada" value="1" {{ old('procesada') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Marcar como procesada</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Si está marcada, ya no se incluirá en la próxima liquidación</p>
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('nomina.novedades.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <div class="flex gap-3">
                    <button type="submit" name="accion" value="guardar" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Guardar Novedad
                    </button>
                    <button type="submit" name="accion" value="guardar_nuevo" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        Guardar y Crear Otra
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cantidadInput = document.getElementById('cantidad_input');
    const valorUnitarioInput = document.getElementById('valor_unitario_input');
    const valorTotalDisplay = document.getElementById('valor_total_display');
    const conceptoSelect = document.getElementById('concepto_select');

    // Calcular valor total
    function calcularTotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const valorUnitario = parseFloat(valorUnitarioInput.value) || 0;
        const total = cantidad * valorUnitario;
        
        valorTotalDisplay.textContent = '$' + total.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    // Auto-llenar valor unitario según concepto
    conceptoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const valorDefecto = selectedOption.getAttribute('data-valor-defecto');
        
        if (valorDefecto && valorDefecto > 0) {
            valorUnitarioInput.value = valorDefecto;
            calcularTotal();
        }
    });

    // Eventos de cálculo
    cantidadInput.addEventListener('input', calcularTotal);
    valorUnitarioInput.addEventListener('input', calcularTotal);

    // Calcular al cargar
    calcularTotal();
});
</script>
@endpush
@endsection