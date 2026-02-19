<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Asignación de Centros de Costo</h2>
        <div class="mt-2 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-700">
                <span class="font-semibold">Empleado:</span> {{ $empleado->nombre_completo }}
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-semibold">Documento:</span> {{ $empleado->numero_documento }}
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-semibold">Cargo:</span> {{ $empleado->cargo }}
            </p>
        </div>
    </div>

    {{-- Mensajes --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabla de centros de costo asignados --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Centros de Costo Asignados</h3>
        </div>

        @if(count($asignaciones) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Centro de Costo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Porcentaje</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desde</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($asignaciones as $index => $asignacion)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm font-semibold text-gray-900">{{ $asignacion['codigo'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-900">{{ $asignacion['nombre'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <input 
                                        type="number" 
                                        wire:model.lazy="asignaciones.{{ $index }}.porcentaje"
                                        wire:change="actualizarPorcentaje({{ $asignacion['id'] }}, $event.target.value)"
                                        min="0.01" 
                                        max="100" 
                                        step="0.01"
                                        class="w-20 px-2 py-1 border border-gray-300 rounded text-center"
                                    >
                                    <span class="text-sm text-gray-600">%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($asignacion['fecha_inicio'])->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button 
                                    wire:click="eliminarCentro({{ $asignacion['id'] }})" 
                                    class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('¿Está seguro de eliminar esta asignación?')"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-semibold text-gray-800">
                            Total:
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-lg font-bold {{ $totalPorcentaje == 100 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($totalPorcentaje, 2) }}%
                            </span>
                        </td>
                        <td colspan="2" class="px-6 py-4 text-right">
                            @if($totalPorcentaje != 100)
                                <span class="text-sm text-red-600">
                                    ⚠️ Debe sumar 100%
                                </span>
                            @else
                                <span class="text-sm text-green-600">
                                    ✓ Distribución válida
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="px-6 py-8 text-center text-gray-500">
                <p>No hay centros de costo asignados</p>
            </div>
        @endif
    </div>

    {{-- Indicador de porcentaje restante --}}
    @if(count($asignaciones) > 0 && $porcentajeRestante > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800">
                <span class="font-semibold">Porcentaje disponible:</span> {{ number_format($porcentajeRestante, 2) }}%
            </p>
        </div>
    @endif

    {{-- Formulario para agregar nuevo centro de costo --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Agregar Centro de Costo</h3>

        <form wire:submit.prevent="agregarCentro">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-7">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Centro de Costo *</label>
                    <select wire:model="centroCostoId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione un centro de costo...</option>
                        @foreach($centrosDisponibles as $centro)
                            <option value="{{ $centro->id }}">
                                {{ $centro->codigo }} - {{ $centro->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('centroCostoId') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Porcentaje *</label>
                    <div class="relative">
                        <input 
                            type="number" 
                            wire:model="porcentaje" 
                            min="0.01" 
                            max="100" 
                            step="0.01"
                            placeholder="0.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg pr-8 focus:ring-2 focus:ring-blue-500"
                        >
                        <span class="absolute right-3 top-2 text-gray-500">%</span>
                    </div>
                    @error('porcentaje') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="md:col-span-2 flex items-end">
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
        </form>

        {{-- Botón de distribución equitativa --}}
        @if(count($asignaciones) > 1)
            <div class="mt-4 pt-4 border-t">
                <button 
                    wire:click="distribuirEquitativamente" 
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    Distribuir equitativamente ({{ number_format(100 / count($asignaciones), 2) }}% cada uno)
                </button>
            </div>
        @endif>
    </div>

    {{-- Ayuda --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-blue-800 mb-2">📝 Información</h4>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• La suma de los porcentajes debe ser exactamente 100%</li>
            <li>• Puede asignar un empleado a múltiples centros de costo</li>
            <li>• Los cambios se guardan automáticamente</li>
            <li>• La distribución se aplicará en la liquidación de nómina</li>
        </ul>
    </div>
</div>