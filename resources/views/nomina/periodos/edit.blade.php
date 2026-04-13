<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Período: {{ $periodo->nombre ?? $periodo->codigo }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <!-- Información del Período -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Período</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $periodo->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Código</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $periodo->codigo }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Estado</p>
                            <p class="font-semibold">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($periodo->estado === 'abierto')
                                        bg-green-100 text-green-800
                                    @elseif($periodo->estado === 'cerrado')
                                        bg-red-100 text-red-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst($periodo->estado) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tipo de Nómina</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $periodo->tipoNomina->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="{{ route('nomina.periodos.update', $periodo) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Fecha Inicio -->
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio', $periodo->fecha_inicio->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('fecha_inicio') border-red-500 @enderror" required>
                            @error('fecha_inicio')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha Fin -->
                        <div>
                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de Fin <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin', $periodo->fecha_fin->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('fecha_fin') border-red-500 @enderror" required>
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
                        <textarea name="observaciones" id="observaciones" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('observaciones') border-red-500 @enderror">{{ old('observaciones', $periodo->observaciones) }}</textarea>
                        @error('observaciones')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Guardar Cambios
                        </button>
                        <a href="{{ route('nomina.periodos.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            Cancelar
                        </a>
                    </div>
                </form>

                <!-- Información de Auditoría -->
                @if($periodo->created_at)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                        <p>Creado: {{ $periodo->created_at->format('d/m/Y H:i') }}</p>
                        <p>Última actualización: {{ $periodo->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
