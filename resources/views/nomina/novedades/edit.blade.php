<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Novedad
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- SECCIÓN DE ESTADO Y ACCIONES (FUERA DEL FORMULARIO DE ACTUALIZACIÓN) --}}
            @if($novedad->estado === 'pendiente')
                <div class="mb-6 p-6 bg-blue-50 dark:bg-blue-900 rounded-lg border-2 border-blue-200 dark:border-blue-700 shadow">
                    <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">
                        ⚙️ CAMBIAR ESTADO DE LA NOVEDAD
                    </h3>
                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">
                        Esta novedad está <strong>pendiente</strong>. Elige una acción:
                    </p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        {{-- FORMULARIO APROBAR (INDEPENDIENTE) --}}
                        <form method="POST" action="{{ route('nomina.novedades.aprobar', $novedad->id) }}">
                            @csrf
                            <button type="submit" class="w-full px-6 py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition shadow-lg transform hover:scale-105">
                                ✓ APROBAR NOVEDAD
                            </button>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-2 text-center">
                                Cambiar estado a: <strong>Aprobada</strong>
                            </p>
                        </form>
                        
                        {{-- BOTÓN PARA MOSTRAR RECHAZO --}}
                        <button type="button" onclick="document.getElementById('rechazarForm').classList.toggle('hidden')" 
                                class="w-full px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition shadow-lg transform hover:scale-105">
                            ✗ RECHAZAR NOVEDAD
                        </button>
                    </div>

                    {{-- FORMULARIO DE RECHAZO (INDEPENDIENTE, OCULTO) --}}
                    <form id="rechazarForm" method="POST" action="{{ route('nomina.novedades.rechazar', $novedad->id) }}" class="hidden mt-4 p-4 bg-red-100 dark:bg-red-800 rounded-lg">
                        @csrf
                        <label class="block text-sm font-bold text-red-900 dark:text-red-100 mb-2">
                            📝 Motivo del rechazo (requerido):
                        </label>
                        <textarea name="motivo_rechazo" rows="3" required 
                                  class="w-full px-3 py-2 border-2 border-red-300 dark:border-red-600 rounded-lg dark:bg-red-900 dark:text-white font-mono text-sm" 
                                  placeholder="Explique por qué rechaza esta novedad..."></textarea>
                        <div class="flex gap-2 mt-3">
                            <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                                Confirmar Rechazo
                            </button>
                            <button type="button" onclick="document.getElementById('rechazarForm').classList.add('hidden')" 
                                    class="flex-1 px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white font-bold rounded-lg transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form id="editForm" action="{{ route('nomina.novedades.update', $novedad->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Empleado -->
                        <div>
                            <label for="empleado_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Empleado <span class="text-red-500">*</span>
                            </label>
                            <select name="empleado_id" id="empleado_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('empleado_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar empleado --</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id }}" {{ old('empleado_id', $novedad->empleado_id) == $empleado->id ? 'selected' : '' }}>
                                        {{ $empleado->primer_nombre }} {{ $empleado->primer_apellido }} ({{ $empleado->numero_documento }})
                                    </option>
                                @endforeach
                            </select>
                            @error('empleado_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Concepto -->
                        <div>
                            <label for="concepto_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Concepto <span class="text-red-500">*</span>
                            </label>
                            <select name="concepto_id" id="concepto_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('concepto_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar concepto --</option>
                                @foreach($conceptos as $concepto)
                                    <option value="{{ $concepto->id }}" {{ old('concepto_id', $novedad->concepto_id) == $concepto->id ? 'selected' : '' }}>
                                        {{ $concepto->codigo }} - {{ $concepto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('concepto_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Período -->
                        <div>
                            <label for="periodo_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Período <span class="text-red-500">*</span>
                            </label>
                            <select name="periodo_id" id="periodo_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('periodo_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar período --</option>
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" {{ old('periodo_id', $novedad->periodo_id) == $periodo->id ? 'selected' : '' }}>
                                        {{ $periodo->codigo ? $periodo->codigo : ($periodo->anio . '-' . str_pad($periodo->mes, 2, '0', STR_PAD_LEFT)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('periodo_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha" id="fecha" value="{{ old('fecha', $novedad->fecha->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('fecha') border-red-500 @enderror" required>
                            @error('fecha')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label for="cantidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="cantidad" id="cantidad" step="0.01" value="{{ old('cantidad', $novedad->cantidad) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('cantidad') border-red-500 @enderror" required>
                            @error('cantidad')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor Unitario -->
                        <div>
                            <label for="valor_unitario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Valor Unitario
                            </label>
                            <input type="number" name="valor_unitario" id="valor_unitario" step="0.01" value="{{ old('valor_unitario', $novedad->valor_unitario) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('valor_unitario') border-red-500 @enderror">
                            @error('valor_unitario')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor Total -->
                        <div>
                            <label for="valor_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Valor Total <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="valor_total" id="valor_total" step="0.01" value="{{ old('valor_total', $novedad->valor_total) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('valor_total') border-red-500 @enderror" required>
                            @error('valor_total')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mt-6">
                        <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Observaciones
                        </label>
                        <textarea name="observaciones" id="observaciones" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('observaciones') border-red-500 @enderror">{{ old('observaciones', $novedad->observaciones) }}</textarea>
                        @error('observaciones')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estado de la novedad (solo lectura) --}}
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-l-4" 
                         @if($novedad->estado === 'pendiente') style="border-color: #fbbf24;"
                         @elseif($novedad->estado === 'aprobada') style="border-color: #3b82f6;"
                         @elseif($novedad->estado === 'aplicada') style="border-color: #10b981;"
                         @elseif($novedad->estado === 'rechazada') style="border-color: #ef4444;"
                         @endif>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>Estado Actual:</strong> 
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                @if($novedad->estado === 'pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($novedad->estado === 'aprobada') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($novedad->estado === 'aplicada') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($novedad->estado === 'rechazada') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @endif">
                                {{ ucfirst($novedad->estado) }}
                            </span>
                        </p>
                        @if($novedad->motivo_rechazo)
                            <p class="text-sm text-red-700 dark:text-red-300 mt-2">
                                <strong>Motivo del rechazo:</strong> {{ $novedad->motivo_rechazo }}
                            </p>
                        @endif
                        @if($novedad->aprobado_by)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                Aprobado por: {{ $novedad->approver->name ?? 'Usuario ID ' . $novedad->aprobado_by }} 
                                @if($novedad->fecha_aprobacion)
                                    el {{ $novedad->fecha_aprobacion->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        @endif
                    </div>

                    @if($novedad->estado === 'pendiente')
                        <div class="mt-8 p-4 bg-amber-50 dark:bg-amber-900 rounded-lg">
                            <p class="text-sm text-amber-800 dark:text-amber-200 mb-3">
                                💾 <strong>Para actualizar los datos de esta novedad</strong>, modifica los campos de arriba y haz clic en "Actualizar Novedad"
                            </p>
                        </div>
                    @endif

                    <!-- Botones del formulario de actualización -->
                    <div class="mt-6 flex gap-3 flex-wrap">
                        @if($novedad->estado === 'pendiente')
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
                                💾 Actualizar Novedad
                            </button>
                            <form method="POST" action="{{ route('nomina.novedades.destroy', $novedad->id) }}" onsubmit="return confirm('¿Está seguro de eliminar esta novedad?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-semibold">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        @else
                            <button type="submit" disabled class="px-6 py-2 bg-gray-400 text-gray-700 rounded-lg cursor-not-allowed font-semibold">
                                Actualizar Novedad (no editable)
                            </button>
                        @endif
                        <a href="{{ route('nomina.novedades.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-semibold">
                            ← Atrás
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
