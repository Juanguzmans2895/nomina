<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Conceptos Fijos del Empleado</h2>
        <div class="mt-2 p-4 bg-blue-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Empleado</p>
                    <p class="font-semibold text-gray-900">{{ $empleado->nombre_completo }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Documento</p>
                    <p class="font-semibold text-gray-900">{{ $empleado->numero_documento }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Salario Básico</p>
                    <p class="font-semibold text-green-600">${{ number_format($empleado->salario_basico, 0) }}</p>
                </div>
            </div>
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

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Devengados</p>
                    <h3 class="text-2xl font-bold text-green-600">
                        ${{ number_format($totalDevengados, 0) }}
                    </h3>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Deducciones</p>
                    <h3 class="text-2xl font-bold text-red-600">
                        ${{ number_format($totalDeducidos, 0) }}
                    </h3>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Neto Estimado</p>
                    <h3 class="text-2xl font-bold text-blue-600">
                        ${{ number_format($netoEstimado, 0) }}
                    </h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Conceptos Asignados --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Devengados --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-green-50 border-b border-green-200">
                <h3 class="text-lg font-semibold text-green-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Devengados
                </h3>
            </div>

            <div class="p-4">
                @php
                    $devengados = collect($conceptosFijos)->where('clasificacion', 'devengado');
                @endphp

                @if($devengados->count() > 0)
                    <div class="space-y-3">
                        @foreach($devengados as $concepto)
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $concepto['nombre'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $concepto['codigo'] }}</p>
                                    </div>
                                    <button 
                                        wire:click="eliminarConcepto({{ $concepto['id'] }})" 
                                        class="text-red-600 hover:text-red-800"
                                        onclick="return confirm('¿Eliminar este concepto?')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">
                                        @if($concepto['porcentaje'])
                                            {{ $concepto['porcentaje'] }}% del salario
                                        @else
                                            Valor fijo
                                        @endif
                                    </span>
                                    <span class="font-semibold text-green-600">
                                        ${{ number_format($concepto['valor_calculado'], 0) }}
                                    </span>
                                </div>
                                @if($concepto['observaciones'])
                                    <p class="text-xs text-gray-500 mt-2">{{ $concepto['observaciones'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">No hay conceptos devengados</p>
                @endif
            </div>
        </div>

        {{-- Deducciones --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                <h3 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Deducciones
                </h3>
            </div>

            <div class="p-4">
                @php
                    $deducidos = collect($conceptosFijos)->where('clasificacion', 'deducido');
                @endphp

                @if($deducidos->count() > 0)
                    <div class="space-y-3">
                        @foreach($deducidos as $concepto)
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $concepto['nombre'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $concepto['codigo'] }}</p>
                                    </div>
                                    <button 
                                        wire:click="eliminarConcepto({{ $concepto['id'] }})" 
                                        class="text-red-600 hover:text-red-800"
                                        onclick="return confirm('¿Eliminar este concepto?')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">
                                        @if($concepto['porcentaje'])
                                            {{ $concepto['porcentaje'] }}% del salario
                                        @else
                                            Valor fijo
                                        @endif
                                    </span>
                                    <span class="font-semibold text-red-600">
                                        ${{ number_format($concepto['valor_calculado'], 0) }}
                                    </span>
                                </div>
                                @if($concepto['observaciones'])
                                    <p class="text-xs text-gray-500 mt-2">{{ $concepto['observaciones'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">No hay conceptos deducidos</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Formulario para agregar nuevo concepto --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Agregar Concepto Fijo</h3>

        <form wire:submit.prevent="agregarConcepto">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Concepto *</label>
                    <select wire:model="conceptoId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione un concepto...</option>
                        @foreach($conceptosDisponibles as $concepto)
                            <option value="{{ $concepto->id }}">
                                {{ $concepto->codigo }} - {{ $concepto->nombre }}
                                @if($concepto->clasificacion === 'devengado')
                                    (+)
                                @else
                                    (-)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('conceptoId') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                    <select wire:model="tipoValor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="valor">Valor Fijo</option>
                        <option value="porcentaje">Porcentaje</option>
                    </select>
                </div>

                @if($tipoValor === 'valor')
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                wire:model="valor" 
                                min="0" 
                                step="1"
                                placeholder="0"
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        @error('valor') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                @else
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Porcentaje *</label>
                        <div class="relative">
                            <input 
                                type="number" 
                                wire:model="porcentaje" 
                                min="0" 
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
                @endif

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

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                <textarea 
                    wire:model="observaciones" 
                    rows="2" 
                    placeholder="Observaciones opcionales..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>
        </form>
    </div>

    {{-- Información --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-blue-800 mb-2">📝 Información</h4>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• Los conceptos fijos se aplicarán automáticamente en cada liquidación</li>
            <li>• Los valores de tipo porcentaje se calculan sobre el salario básico</li>
            <li>• Los conceptos devengados incrementan el pago (+)</li>
            <li>• Los conceptos deducidos disminuyen el pago (-)</li>
            <li>• Puede modificar o eliminar los conceptos en cualquier momento</li>
        </ul>
    </div>
</div>