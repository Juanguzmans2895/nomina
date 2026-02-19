<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nuevo Concepto de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Crear Nuevo Concepto</h1>
                        <a href="{{ route('nomina.conceptos.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400">
                            ← Volver
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('nomina.conceptos.store') }}">
                        @csrf

                        {{-- Información Básica --}}
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Información Básica</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código *</label>
                                    <input type="text" name="codigo" value="{{ old('codigo') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre *</label>
                                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                                    <textarea name="descripcion" rows="2"
                                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">{{ old('descripcion') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clasificación *</label>
                                    <select name="clasificacion" required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                        <option value="">Seleccione...</option>
                                        <option value="DEVENGADO" {{ old('clasificacion') == 'DEVENGADO' ? 'selected' : '' }}>Devengado</option>
                                        <option value="DEDUCIDO" {{ old('clasificacion') == 'DEDUCIDO' ? 'selected' : '' }}>Deducido</option>
                                        <option value="NO_IMPUTABLE" {{ old('clasificacion') == 'NO_IMPUTABLE' ? 'selected' : '' }}>No Imputable</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo *</label>
                                    <select name="tipo" required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                        <option value="">Seleccione...</option>
                                        <option value="FIJO" {{ old('tipo') == 'FIJO' ? 'selected' : '' }}>Fijo</option>
                                        <option value="VARIABLE" {{ old('tipo') == 'VARIABLE' ? 'selected' : '' }}>Variable</option>
                                        <option value="CALCULADO" {{ old('tipo') == 'CALCULADO' ? 'selected' : '' }}>Calculado</option>
                                        <option value="NOVEDAD" {{ old('tipo') == 'NOVEDAD' ? 'selected' : '' }}>Novedad</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Configuración --}}
                        <div class="mb-6 border-t dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Configuración</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="base_salarial" value="1" {{ old('base_salarial') ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Hace parte de la base salarial</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="afecta_prestaciones" value="1" {{ old('afecta_prestaciones') ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Afecta prestaciones sociales</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="afecta_seguridad_social" value="1" {{ old('afecta_seguridad_social') ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Afecta seguridad social</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="afecta_parafiscales" value="1" {{ old('afecta_parafiscales') ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Afecta parafiscales</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="aplica_retencion" value="1" {{ old('aplica_retencion') ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Aplica retención en la fuente</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="visible_colilla" value="1" {{ old('visible_colilla', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Visible en colilla</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Valores --}}
                        <div class="mb-6 border-t dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Valores y Cálculo</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Porcentaje (%)</label>
                                    <input type="number" name="porcentaje" value="{{ old('porcentaje') }}" step="0.01" min="0" max="100"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Fijo</label>
                                    <input type="number" name="valor_fijo" value="{{ old('valor_fijo') }}" step="0.01" min="0"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Orden en Colilla</label>
                                    <input type="number" name="orden_colilla" value="{{ old('orden_colilla', 100) }}" min="0"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Agrupador</label>
                                    <input type="text" name="agrupador" value="{{ old('agrupador') }}"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg"
                                           placeholder="Ej: Horas Extras, Prestaciones, Deducciones">
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-end gap-3 pt-6 border-t dark:border-gray-700">
                            <a href="{{ route('nomina.conceptos.index') }}" 
                               class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Guardar Concepto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>