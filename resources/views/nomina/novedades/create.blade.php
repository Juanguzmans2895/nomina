<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Crear Nueva Novedad
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form action="{{ route('nomina.novedades.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Empleado -->
                        <div>
                            <label for="empleado_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Empleado <span class="text-red-500">*</span>
                            </label>
                            <select name="empleado_id" id="empleado_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('empleado_id') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar empleado --</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id }}" {{ old('empleado_id') == $empleado->id ? 'selected' : '' }}>
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
                                    <option value="{{ $concepto->id }}" {{ old('concepto_id') == $concepto->id ? 'selected' : '' }}>
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
                                    <option value="{{ $periodo->id }}" {{ old('periodo_id') == $periodo->id ? 'selected' : '' }}>
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
                            <input type="date" name="fecha" id="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('fecha') border-red-500 @enderror" required>
                            @error('fecha')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label for="cantidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="cantidad" id="cantidad" step="0.01" value="{{ old('cantidad', 0) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('cantidad') border-red-500 @enderror" required>
                            @error('cantidad')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor Unitario -->
                        <div>
                            <label for="valor_unitario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Valor Unitario
                            </label>
                            <input type="number" name="valor_unitario" id="valor_unitario" step="0.01" value="{{ old('valor_unitario', 0) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('valor_unitario') border-red-500 @enderror">
                            @error('valor_unitario')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor Total -->
                        <div>
                            <label for="valor_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Valor Total <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="valor_total" id="valor_total" step="0.01" value="{{ old('valor_total', 0) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('valor_total') border-red-500 @enderror" required>
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
                        <textarea name="observaciones" id="observaciones" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('observaciones') border-red-500 @enderror">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Guardar Novedad
                        </button>
                        <a href="{{ route('nomina.novedades.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
