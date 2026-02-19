<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Editar Empleado</h1>
                        <p class="text-gray-600 mt-1">{{ $empleado->nombre_completo ?? 'N/A' }}</p>
                    </div>
                    <a href="{{ route('nomina.empleados.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg dark:bg-red-900 dark:border-red-700">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Hay errores en el formulario:</h3>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Formulario --}}
            <form action="{{ route('nomina.empleados.update', $empleado) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Información Personal --}}
                <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Información Personal</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Primer Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="primer_nombre" value="{{ old('primer_nombre', $empleado->primer_nombre) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('primer_nombre')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" value="{{ old('segundo_nombre', $empleado->segundo_nombre) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Primer Apellido <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="primer_apellido" value="{{ old('primer_apellido', $empleado->primer_apellido) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('primer_apellido')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido', $empleado->segundo_apellido) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Documento <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo_documento" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccionar...</option>
                                <option value="CC" {{ old('tipo_documento', $empleado->tipo_documento) == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="CE" {{ old('tipo_documento', $empleado->tipo_documento) == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                <option value="TI" {{ old('tipo_documento', $empleado->tipo_documento) == 'TI' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                                <option value="PA" {{ old('tipo_documento', $empleado->tipo_documento) == 'PA' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                            @error('tipo_documento')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Documento <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento', $empleado->numero_documento) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('numero_documento')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Género <span class="text-red-500">*</span>
                            </label>
                            <select name="genero" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccionar...</option>
                                <option value="M" {{ old('genero', $empleado->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('genero', $empleado->genero) == 'F' ? 'selected' : '' }}>Femenino</option>
                                <option value="O" {{ old('genero', $empleado->genero) == 'O' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('genero')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $empleado->fecha_nacimiento?->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $empleado->email) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $empleado->telefono) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $empleado->direccion) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Información Laboral --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Información Laboral</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código de Empleado</label>
                            <input type="text" name="codigo_empleado" value="{{ old('codigo_empleado', $empleado->codigo_empleado) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cargo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="cargo" value="{{ old('cargo', $empleado->cargo) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dependencia</label>
                            <input type="text" name="dependencia" value="{{ old('dependencia', $empleado->dependencia) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Ingreso <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', $empleado->fecha_ingreso?->format('Y-m-d')) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Contrato <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo_contrato" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccionar...</option>
                                <option value="indefinido" {{ old('tipo_contrato', $empleado->tipo_contrato) == 'indefinido' ? 'selected' : '' }}>Indefinido</option>
                                <option value="fijo" {{ old('tipo_contrato', $empleado->tipo_contrato) == 'fijo' ? 'selected' : '' }}>Término Fijo</option>
                                <option value="obra_labor" {{ old('tipo_contrato', $empleado->tipo_contrato) == 'obra_labor' ? 'selected' : '' }}>Obra o Labor</option>
                                <option value="prestacion_servicios" {{ old('tipo_contrato', $empleado->tipo_contrato) == 'prestacion_servicios' ? 'selected' : '' }}>Prestación de Servicios</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Salario Básico <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="salario_basico" value="{{ old('salario_basico', $empleado->salario_basico) }}" required step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Clase de Riesgo (ARL) <span class="text-red-500">*</span>
                            </label>
                            <select name="clase_riesgo" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccionar...</option>
                                <option value="0.522" {{ old('clase_riesgo', $empleado->clase_riesgo) == '0.522' ? 'selected' : '' }}>Clase I - 0.522% (Riesgo Mínimo)</option>
                                <option value="1.044" {{ old('clase_riesgo', $empleado->clase_riesgo) == '1.044' ? 'selected' : '' }}>Clase II - 1.044% (Riesgo Bajo)</option>
                                <option value="2.436" {{ old('clase_riesgo', $empleado->clase_riesgo) == '2.436' ? 'selected' : '' }}>Clase III - 2.436% (Riesgo Medio)</option>
                                <option value="4.350" {{ old('clase_riesgo', $empleado->clase_riesgo) == '4.350' ? 'selected' : '' }}>Clase IV - 4.350% (Riesgo Alto)</option>
                                <option value="6.960" {{ old('clase_riesgo', $empleado->clase_riesgo) == '6.960' ? 'selected' : '' }}>Clase V - 6.960% (Riesgo Máximo)</option>
                            </select>
                            @error('clase_riesgo')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                            <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="activo" {{ old('estado', $empleado->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', $empleado->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="retirado" {{ old('estado', $empleado->estado) == 'retirado' ? 'selected' : '' }}>Retirado</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Información de Seguridad Social --}}
                <div class="bg-white dark:bg-gray-800 dark:rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Seguridad Social</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">EPS</label>
                            <input type="text" name="eps" value="{{ old('eps', $empleado->eps) }}"
                                placeholder="Ej: Sura, Sanitas, Compensar"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fondo de Pensión</label>
                            <input type="text" name="fondo_pension" value="{{ old('fondo_pension', $empleado->fondo_pension) }}"
                                placeholder="Ej: Porvenir, Protección, Colfondos"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ARL</label>
                            <input type="text" name="arl" value="{{ old('arl', $empleado->arl) }}"
                                placeholder="Ej: Sura ARL, Positiva, Colmena"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Caja de Compensación</label>
                            <input type="text" name="caja_compensacion" value="{{ old('caja_compensacion', $empleado->caja_compensacion) }}"
                                placeholder="Ej: Compensar, Colsubsidio, Comfama"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('nomina.empleados.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Actualizar Empleado
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>