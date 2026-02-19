<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nuevo Contrato
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <form action="{{ route('nomina.contratos.store') }}" method="POST">
                        @csrf

                        {{-- Información del Contrato --}}
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Información del Contrato</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Contrato *</label>
                                    <input type="text" name="numero_contrato" value="{{ old('numero_contrato') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('numero_contrato')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Contrato *</label>
                                    <select name="tipo_contrato" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                        <option value="prestacion_servicios" {{ old('tipo_contrato') == 'prestacion_servicios' ? 'selected' : '' }}>Prestación de Servicios</option>
                                        <option value="obra_labor" {{ old('tipo_contrato') == 'obra_labor' ? 'selected' : '' }}>Obra o Labor</option>
                                        <option value="suministro" {{ old('tipo_contrato') == 'suministro' ? 'selected' : '' }}>Suministro</option>
                                    </select>
                                    @error('tipo_contrato')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado *</label>
                                    <select name="estado" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                        <option value="borrador" {{ old('estado', 'borrador') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                                        <option value="aprobado" {{ old('estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="en_ejecucion" {{ old('estado') == 'en_ejecucion' ? 'selected' : '' }}>En Ejecución</option>
                                    </select>
                                    @error('estado')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Inicio *</label>
                                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('fecha_inicio')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Fin *</label>
                                    <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('fecha_fin')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plazo (días) *</label>
                                    <input type="number" name="plazo_dias" value="{{ old('plazo_dias') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('plazo_dias')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Objeto del Contrato *</label>
                                <textarea name="objeto" rows="3" required
                                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">{{ old('objeto') }}</textarea>
                                @error('objeto')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Información del Contratista --}}
                        <div class="mb-8 border-t pt-6">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Información del Contratista</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo Documento *</label>
                                    <select name="tipo_documento_contratista" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                        <option value="CC" {{ old('tipo_documento_contratista') == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                        <option value="CE" {{ old('tipo_documento_contratista') == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                        <option value="NIT" {{ old('tipo_documento_contratista') == 'NIT' ? 'selected' : '' }}>NIT</option>
                                        <option value="PA" {{ old('tipo_documento_contratista') == 'PA' ? 'selected' : '' }}>Pasaporte</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número Documento *</label>
                                    <input type="text" name="numero_documento_contratista" value="{{ old('numero_documento_contratista') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('numero_documento_contratista')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre Completo *</label>
                                    <input type="text" name="nombre_contratista" value="{{ old('nombre_contratista') }}" required
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('nombre_contratista')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                    <input type="email" name="email_contratista" value="{{ old('email_contratista') }}"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teléfono</label>
                                    <input type="text" name="telefono_contratista" value="{{ old('telefono_contratista') }}"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ciudad</label>
                                    <input type="text" name="ciudad_contratista" value="{{ old('ciudad_contratista') }}"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
                                <input type="text" name="direccion_contratista" value="{{ old('direccion_contratista') }}"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                            </div>
                        </div>

                        {{-- Valores --}}
                        <div class="mb-8 border-t pt-6">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Valores del Contrato</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Total *</label>
                                    <input type="number" name="valor_total" value="{{ old('valor_total') }}" required step="0.01"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                    @error('valor_total')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Mensual</label>
                                    <input type="number" name="valor_mensual" value="{{ old('valor_mensual') }}" step="0.01"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supervisor</label>
                                    <select name="supervisor_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                        <option value="">Seleccione...</option>
                                        @foreach($supervisores ?? [] as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                                {{ $supervisor->nombre_completo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Retenciones --}}
                        <div class="mb-8 border-t pt-6">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Retenciones</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="aplica_retencion_fuente" value="1" {{ old('aplica_retencion_fuente', true) ? 'checked' : '' }}
                                           class="mr-2 rounded">
                                    <label class="text-sm text-gray-700 dark:text-gray-300">Aplica Retención en la Fuente</label>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Porcentaje Retención (%)</label>
                                    <input type="number" name="porcentaje_retencion_fuente" value="{{ old('porcentaje_retencion_fuente', 10) }}" step="0.01"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-end gap-3 mt-6">
                            <a href="{{ route('nomina.contratos.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Guardar Contrato
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>