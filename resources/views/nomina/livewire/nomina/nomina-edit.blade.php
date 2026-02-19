<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Editar Nómina</h1>
                    <p class="text-gray-600 mt-1 dark:text-gray-400">{{ $nomina->numero_nomina ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('nomina.nominas.detalles', $nomina) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>

            {{-- Errores --}}
            @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg dark:bg-red-900 dark:border-red-700">
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Información del Estado --}}
            @php
                $estadoValue = is_object($nomina->estado) ? $nomina->estado->value : $nomina->estado;
            @endphp

            @if($estadoValue !== 'borrador')
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg dark:bg-yellow-900 dark:border-yellow-700">
                    <p class="text-yellow-800 dark:text-yellow-200">
                        ⚠️ Esta nómina está en estado <strong>{{ ucfirst($estadoValue) }}</strong>. 
                        Solo se pueden editar campos limitados.
                    </p>
                </div>
            @endif

            {{-- Formulario --}}
            <form action="{{ route('nomina.nominas.actualizar', $nomina) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Información General --}}
                <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Información General</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Nómina
                            </label>
                            <input type="text" name="numero_nomina" value="{{ old('numero_nomina', $nomina->numero_nomina) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" value="{{ old('nombre', $nomina->nombre) }}" required
                                {{ $estadoValue !== 'borrador' ? 'readonly' : '' }}
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white {{ $estadoValue !== 'borrador' ? 'bg-gray-50 dark:bg-gray-700 dark:text-white' : 'focus:ring-2 focus:ring-blue-500' }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Nómina
                            </label>
                            <input type="text" 
                                value="{{ $nomina->tipo->nombre ?? 'N/A' }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Período
                            </label>
                            <input type="text" 
                                value="{{ $nomina->periodo->nombre ?? 'N/A' }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>
                    </div>
                </div>

                {{-- Fechas --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Fechas</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_inicio" 
                                value="{{ old('fecha_inicio', $nomina->fecha_inicio?->format('Y-m-d')) }}" required
                                {{ $estadoValue !== 'borrador' ? 'readonly' : '' }}
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white {{ $estadoValue !== 'borrador' ? 'bg-gray-50 dark:bg-gray-700 dark:text-white' : 'focus:ring-2 focus:ring-blue-500' }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha Fin <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_fin" 
                                value="{{ old('fecha_fin', $nomina->fecha_fin?->format('Y-m-d')) }}" required
                                {{ $estadoValue !== 'borrador' ? 'readonly' : '' }}
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white {{ $estadoValue !== 'borrador' ? 'bg-gray-50 dark:bg-gray-700 dark:text-white' : 'focus:ring-2 focus:ring-blue-500' }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Pago <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha_pago" 
                                value="{{ old('fecha_pago', $nomina->fecha_pago?->format('Y-m-d')) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Totales (Solo lectura) --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Totales</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Devengado</label>
                            <input type="text" 
                                value="${{ number_format($nomina->total_devengado ?? 0, 0) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Deducciones</label>
                            <input type="text" 
                                value="${{ number_format($nomina->total_deducciones ?? 0, 0) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Neto</label>
                            <input type="text" 
                                value="${{ number_format($nomina->total_neto ?? 0, 0) }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono font-bold dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empleados</label>
                            <input type="text" 
                                value="{{ $nomina->numero_empleados ?? 0 }}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Observaciones</h2>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas</label>
                        <textarea name="observaciones" rows="4" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('observaciones', $nomina->observaciones) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Notas internas sobre esta nómina</p>
                    </div>
                </div>

                {{-- Detalles por Empleado --}}
                @if(isset($nomina->detalles) && count($nomina->detalles) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        Detalles por Empleado
                        <span class="text-sm font-normal text-gray-500">({{ count($nomina->detalles) }} empleados)</span>
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Empleado</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Salario</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Devengado</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Deducciones</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-300">Neto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800">
                                @foreach($nomina->detalles as $detalle)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-4 py-3 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $detalle->empleado->nombre_completo ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $detalle->empleado->numero_documento ?? 'N/A' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-100">
                                        ${{ number_format($detalle->salario_basico ?? 0, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-mono text-blue-600 dark:text-blue-400">
                                        ${{ number_format($detalle->total_devengado ?? 0, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-mono text-red-600 dark:text-red-400">
                                        ${{ number_format($detalle->total_deducciones ?? 0, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-mono font-bold text-green-600 dark:text-green-400">
                                        ${{ number_format($detalle->total_neto ?? 0, 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Botones de acción --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('nomina.nominas.detalles', $nomina) }}" 
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-black dark:text-white transition">
                        Cancelar
                    </a>

                    <div class="flex gap-4">
                        @if($estadoValue === 'borrador')
                            <button type="submit" name="accion" value="guardar" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Guardar Cambios
                            </button>
                            <button type="submit" name="accion" value="guardar_aprobar" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                Guardar y Aprobar
                            </button>
                        @else
                            <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Actualizar
                            </button>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Historial de Cambios (si existe) --}}
            @if(isset($nomina->auditorias) && count($nomina->auditorias) > 0)
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Historial de Cambios</h2>
                
                <div class="space-y-3">
                    @foreach($nomina->auditorias as $auditoria)
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $auditoria->evento }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $auditoria->usuario->name ?? 'Sistema' }}</p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $auditoria->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        @if($auditoria->cambios)
                            <p class="text-xs text-gray-500 mt-1 dark:text-gray-400">{{ $auditoria->cambios }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>