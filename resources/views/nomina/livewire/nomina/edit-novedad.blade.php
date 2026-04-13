<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Novedad de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Editar Novedad</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Modificar información de la novedad</p>
                        </div>
                        <a href="{{ route('nomina.novedades.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400">
                            ← Volver
                        </a>
                    </div>

                    {{-- Mensajes de error --}}
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Alerta si está procesada --}}
                    @if($novedad->procesada)
                        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 px-4 py-3 rounded mb-4">
                            <p class="font-semibold">⚠️ Novedad Procesada</p>
                            <p class="text-sm">Esta novedad ya ha sido procesada. Los cambios pueden afectar cálculos existentes.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('nomina.novedades.update', $novedad) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            {{-- Sección 1: Información del Empleado --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                    Información del Empleado
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Empleado *
                                        </label>
                                        <select name="empleado_id" required 
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                {{ $novedad->procesada ? 'disabled' : '' }}>
                                            <option value="">Seleccione un empleado...</option>
                                            @foreach($empleados as $empleado)
                                                <option value="{{ $empleado->id }}" 
                                                        {{ (old('empleado_id', $novedad->empleado_id) == $empleado->id) ? 'selected' : '' }}>
                                                    {{ $empleado->nombre_completo }} - {{ $empleado->numero_documento }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($novedad->procesada)
                                            <input type="hidden" name="empleado_id" value="{{ $novedad->empleado_id }}">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Sección 2: Concepto de Novedad --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                    Concepto de Novedad
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Concepto *
                                        </label>
                                        <select name="concepto_id" required 
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                {{ $novedad->procesada ? 'disabled' : '' }}>
                                            <option value="">Seleccione un concepto...</option>
                                            @foreach($conceptos as $concepto)
                                                <option value="{{ $concepto->id }}" 
                                                        {{ (old('concepto_id', $novedad->concepto_id) == $concepto->id) ? 'selected' : '' }}>
                                                    {{ $concepto->codigo }} - {{ $concepto->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($novedad->procesada)
                                            <input type="hidden" name="concepto_id" value="{{ $novedad->concepto_id }}">
                                        @endif
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Período (opcional)
                                        </label>
                                        <select name="periodo_id" 
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                            <option value="">Sin período específico</option>
                                            @foreach($periodos as $periodo)
                                                <option value="{{ $periodo->id }}" 
                                                        {{ (old('periodo_id', $novedad->periodo_id) == $periodo->id) ? 'selected' : '' }}>
                                                    {{ $periodo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Fecha de Novedad *
                                        </label>
                                        <input type="date" name="fecha" 
                                               value="{{ old('fecha', $novedad->fecha) }}" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                    </div>
                                </div>
                            </div>

                            {{-- Sección 3: Valores --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                    Valores
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Cantidad *
                                        </label>
                                        <input type="number" name="cantidad" 
                                               value="{{ old('cantidad', $novedad->cantidad) }}" 
                                               step="0.01" min="0" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Valor Unitario *
                                        </label>
                                        <input type="number" name="valor_unitario" 
                                               value="{{ old('valor_unitario', $novedad->valor_unitario) }}" 
                                               step="0.01" min="0" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Valor Total (calculado)
                                        </label>
                                        <input type="text" id="valor_total_display" readonly
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-lg"
                                               value="$ {{ number_format($novedad->valor_total, 2) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Sección 4: Observaciones --}}
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                    Información Adicional
                                </h3>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Observaciones
                                    </label>
                                    <textarea name="observaciones" rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">{{ old('observaciones', $novedad->observaciones) }}</textarea>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="procesada" value="1" 
                                               {{ old('procesada', $novedad->procesada) ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Marcar como procesada
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex justify-between pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                            <form method="POST" action="{{ route('nomina.novedades.destroy', $novedad) }}" 
                                  onsubmit="return confirm('¿Está seguro de eliminar esta novedad?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Eliminar Novedad
                                </button>
                            </form>

                            <div class="flex gap-3">
                                <a href="{{ route('nomina.novedades.index') }}" 
                                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Cancelar
                                </a>
                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Actualizar Novedad
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Calcular valor total automáticamente
        function calcularTotal() {
            const cantidad = parseFloat(document.querySelector('input[name="cantidad"]').value) || 0;
            const valorUnitario = parseFloat(document.querySelector('input[name="valor_unitario"]').value) || 0;
            const total = cantidad * valorUnitario;
            
            document.getElementById('valor_total_display').value = '$ ' + total.toLocaleString('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.querySelector('input[name="cantidad"]').addEventListener('input', calcularTotal);
        document.querySelector('input[name="valor_unitario"]').addEventListener('input', calcularTotal);
        
        // Calcular al cargar
        calcularTotal();
    </script>
    @endpush
</x-app-layout>