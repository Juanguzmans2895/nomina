<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Concepto de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Editar Concepto</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Modifique la información del concepto de nómina</p>
                        </div>
                        <a href="{{ route('nomina.conceptos.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Volver
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

                    {{-- Formulario --}}
                    <form action="{{ route('nomina.conceptos.update', $concepto) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            {{-- Información Básica --}}
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Información Básica</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Código <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="codigo" value="{{ old('codigo', $concepto->codigo) }}" 
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                               required maxlength="50">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Nombre <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nombre" value="{{ old('nombre', $concepto->nombre) }}" 
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                               required maxlength="200">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Descripción
                                        </label>
                                        <textarea name="descripcion" rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">{{ old('descripcion', $concepto->descripcion) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Clasificación --}}
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Clasificación</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Clasificación <span class="text-red-500">*</span>
                                        </label>
                                        <select name="clasificacion" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg" required>
                                            @php
                                                $clasificacionValue = is_object($concepto->clasificacion) ? $concepto->clasificacion->value : $concepto->clasificacion;
                                            @endphp
                                            <option value="devengado" {{ $clasificacionValue === 'devengado' || $clasificacionValue === 'DEVENGADO' ? 'selected' : '' }}>Devengado</option>
                                            <option value="deducido" {{ $clasificacionValue === 'deducido' || $clasificacionValue === 'DEDUCIDO' ? 'selected' : '' }}>Deducido</option>
                                            <option value="no_imputable" {{ $clasificacionValue === 'no_imputable' || $clasificacionValue === 'NO_IMPUTABLE' ? 'selected' : '' }}>No Imputable</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Tipo <span class="text-red-500">*</span>
                                        </label>
                                        <select name="tipo" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg" required>
                                            @php
                                                $tipoValue = is_object($concepto->tipo) ? $concepto->tipo->value : $concepto->tipo;
                                            @endphp
                                            <option value="fijo" {{ $tipoValue === 'fijo' || $tipoValue === 'FIJO' ? 'selected' : '' }}>Fijo</option>
                                            <option value="novedad" {{ $tipoValue === 'novedad' || $tipoValue === 'NOVEDAD' ? 'selected' : '' }}>Novedad</option>
                                            <option value="calculado" {{ $tipoValue === 'calculado' || $tipoValue === 'CALCULADO' ? 'selected' : '' }}>Calculado</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Porcentaje
                                        </label>
                                        <input type="number" name="porcentaje" value="{{ old('porcentaje', $concepto->porcentaje) }}" 
                                               step="0.01" min="0" max="100"
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            {{-- Afectaciones --}}
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Afectaciones</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="base_salarial" value="1" 
                                               {{ old('base_salarial', $concepto->base_salarial ?? false) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Base Salarial
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="afecta_prestaciones" value="1" 
                                               {{ old('afecta_prestaciones', $concepto->afecta_prestaciones ?? false) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Afecta Prestaciones
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="afecta_seguridad_social" value="1" 
                                               {{ old('afecta_seguridad_social', $concepto->afecta_seguridad_social ?? false) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Afecta Seguridad Social
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="afecta_parafiscales" value="1" 
                                               {{ old('afecta_parafiscales', $concepto->afecta_parafiscales ?? false) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Afecta Parafiscales
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="aplica_retencion" value="1" 
                                               {{ old('aplica_retencion', $concepto->aplica_retencion ?? false) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Aplica Retención
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="activo" value="1" 
                                               {{ old('activo', $concepto->activo ?? true) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Activo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('nomina.conceptos.index') }}" 
                                   class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                    Cancelar
                                </a>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>