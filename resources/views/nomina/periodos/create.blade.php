<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Crear Nuevo Período
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form action="{{ route('nomina.periodos.store') }}" method="POST">
                    @csrf

                    <!-- Tipo de Nómina -->
                    <div class="mb-6">
                        <label for="tipo_nomina_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo de Nómina <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_nomina_id" id="tipo_nomina_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('tipo_nomina_id') border-red-500 @enderror" required>
                            <option value="">-- Seleccionar tipo --</option>
                            @foreach($tiposNomina as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('tipo_nomina_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_nomina_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Mes -->
                        <div>
                            <label for="mes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Mes <span class="text-red-500">*</span>
                            </label>
                            <select name="mes" id="mes" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('mes') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar mes --</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('mes', $sugerenciaMes) == $i ? 'selected' : '' }}>
                                        @php
                                            $meses = [
                                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                            ];
                                        @endphp
                                        {{ $meses[$i] }}
                                    </option>
                                @endfor
                            </select>
                            @error('mes')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Año -->
                        <div>
                            <label for="anio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Año <span class="text-red-500">*</span>
                            </label>
                            <select name="anio" id="anio" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('anio') border-red-500 @enderror" required>
                                <option value="">-- Seleccionar año --</option>
                                @for($year = now()->year + 1; $year >= 2020; $year--)
                                    <option value="{{ $year }}" {{ old('anio', $sugerenciaAnio) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                            @error('anio')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Fecha Inicio -->
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('fecha_inicio') border-red-500 @enderror" required>
                            @error('fecha_inicio')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha Fin -->
                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de Fin <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('fecha_fin') border-red-500 @enderror" required>
                            @error('fecha_fin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-6">
                        <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Observaciones
                        </label>
                        <textarea name="observaciones" id="observaciones" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('observaciones') border-red-500 @enderror">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Crear Período
                        </button>
                        <a href="{{ route('nomina.periodos.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
