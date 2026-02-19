<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva Novedad de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Crear Novedad</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Registre una nueva novedad para un empleado</p>
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

                    <form method="POST" action="{{ route('nomina.novedades.store') }}">
                        @csrf

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
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccione un empleado...</option>
                                            @foreach($empleados as $empleado)
                                                <option value="{{ $empleado->id }}" {{ old('empleado_id') == $empleado->id ? 'selected' : '' }}>
                                                    {{ $empleado->nombre_completo }} - {{ $empleado->numero_documento }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                        <select name="concepto_nomina_id" required 
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccione un concepto...</option>
                                            @foreach($conceptos as $concepto)
                                                <option value="{{ $concepto->id }}" {{ old('concepto_nomina_id') == $concepto->id ? 'selected' : '' }}>
                                                    {{ $concepto->codigo }} - {{ $concepto->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Período (opcional)
                                        </label>
                                        <select name="periodo_nomina_id" 
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                            <option value="">Sin período específico</option>
                                            @foreach($periodos as $periodo)
                                                <option value="{{ $periodo->id }}" {{ old('periodo_nomina_id') == $periodo->id ? 'selected' : '' }}>
                                                    {{ $periodo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Fecha de Novedad *
                                        </label>
                                        <input type="date" name="fecha_novedad" value="{{ old('fecha_novedad', date('Y-m-d')) }}" required
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
                                        <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" 
                                               step="0.01" min="0" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                               placeholder="Horas, días, etc.">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ej: horas, días, unidades</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Valor Unitario *
                                        </label>
                                        <input type="number" name="valor_unitario" value="{{ old('valor_unitario', 0) }}" 
                                               step="0.01" min="0" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                               placeholder="0.00">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Valor por unidad</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Valor Total (calculado)
                                        </label>
                                        <input type="text" id="valor_total_display" readonly
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-lg"
                                               value="$ 0.00">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cantidad × Valor unitario</p>
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
                                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                              placeholder="Información adicional sobre esta novedad...">{{ old('observaciones') }}</textarea>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="procesada" value="1" {{ old('procesada') ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Marcar como procesada
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('nomina.novedades.index') }}" 
                               class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                Cancelar
                            </a>
                            <button type="submit" name="accion" value="guardar_nuevo"
                                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                Guardar y Crear Otra
                            </button>
                            <button type="submit" name="accion" value="guardar"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Guardar Novedad
                            </button>
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