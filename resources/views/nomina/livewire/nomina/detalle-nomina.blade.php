<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalle de Nómina - {{ $nomina->numero_nomina }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Detalle de Nómina</h1>
                    <p class="text-gray-600 mt-1 dark:text-gray-400">{{ $nomina->numero_nomina ?? 'N/A' }}</p>
                </div>
                <a href="{{ route('nomina.nominas.historial') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver al Historial
                </a>
            </div>

            {{-- Información General --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $nomina->nombre ?? 'Nómina' }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Número</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $nomina->numero_nomina ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tipo</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $nomina->tipo->nombre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Período</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $nomina->periodo->nombre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Estado</p>
                        @php
                            $estadoValue = is_object($nomina->estado) ? $nomina->estado->value : $nomina->estado;
                            $estadoNombre = ucfirst($estadoValue ?? 'N/A');
                            
                            $badgeClass = match($estadoValue ?? '') {
                                'borrador' => 'bg-yellow-100 text-yellow-800',
                                'preliquidada' => 'bg-blue-100 text-blue-800',
                                'aprobada' => 'bg-green-100 text-green-800',
                                'pagada' => 'bg-purple-100 text-purple-800',
                                'cerrada' => 'bg-gray-100 text-gray-800',
                                'anulada' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs rounded-full {{ $badgeClass }}">
                            {{ $estadoNombre }}
                        </span>
                    </div>
                </div>

                {{-- Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 border-t pt-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Fecha Inicio</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-200">
                            {{ $nomina->fecha_inicio ? $nomina->fecha_inicio->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Fecha Fin</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-200">
                            {{ $nomina->fecha_fin ? $nomina->fecha_fin->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Fecha Pago</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-200">
                            {{ $nomina->fecha_pago ? $nomina->fecha_pago->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Totales --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Devengado</p>
                        <p class="text-2xl font-bold text-blue-600">
                            ${{ number_format($nomina->total_devengado ?? 0, 0) }}
                        </p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Deducciones</p>
                        <p class="text-2xl font-bold text-red-600">
                            ${{ number_format($nomina->total_deducciones ?? 0, 0) }}
                        </p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Neto</p>
                        <p class="text-2xl font-bold text-green-600">
                            ${{ number_format($nomina->total_neto ?? 0, 0) }}
                        </p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Empleados</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $nomina->numero_empleados ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Detalles por Empleado --}}
            <div class="bg-white rounded-lg dark:bg-gray-800 dark:shadow overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">Detalles por Empleado</h3>
                        <a href="{{ route('nomina.reportes.consolidado', $nomina->id) }}" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimir Consolidado Nomina
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Empleado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Salario</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Devengado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deducciones</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Neto</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($nomina->detallesNomina ?? [] as $detalle)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-200">{{ $detalle->empleado->nombre_completo ?? 'N/A' }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $detalle->empleado->numero_documento ?? 'N/A' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900 dark:text-gray-200">
                                        ${{ number_format($detalle->salario_basico ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-blue-600 dark:text-blue-400">
                                        ${{ number_format($detalle->total_devengado ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-red-600 dark:text-red-400">
                                        ${{ number_format($detalle->total_deducciones ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-green-600 dark:text-green-400">
                                        ${{ number_format($detalle->total_neto ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('nomina.reportes.desprendible', $detalle) }}" 
                                        class="text-blue-600 hover:text-blue-900" title="Ver desprendible">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-300">No hay detalles</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay empleados registrados en esta nómina</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if(isset($nomina->detallesNomina) && count($nomina->detallesNomina) > 0)
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr class="font-bold">
                                    <td class="px-6 py-4 text-left text-gray-900 dark:text-gray-200">TOTALES</td>
                                    <td class="px-6 py-4 text-right text-gray-900 dark:text-gray-200 font-mono">
                                        ${{ number_format($nomina->detallesNomina->sum('salario_basico'), 0) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-blue-600 dark:text-blue-400 font-mono">
                                        ${{ number_format($nomina->total_devengado ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-red-600 dark:text-red-400 font-mono">
                                        ${{ number_format($nomina->total_deducciones ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-green-600 dark:text-green-400 font-mono">
                                        ${{ number_format($nomina->total_neto ?? 0, 0) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('nomina.nominas.historial') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white dark:text-white dark:hover:bg-gray-800 rounded-lg transition">
                    ← Volver al Historial
                </a>
                
                @php
                    $estadoVal = is_object($nomina->estado) ? $nomina->estado->value : $nomina->estado;
                @endphp
                
                @if($estadoVal === 'borrador')
                <div class="flex gap-2">
                    <a href="{{ route('nomina.nominas.editar', $nomina) }}" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white dark:text-white dark:hover:bg-blue-800 rounded-lg transition">
                        Editar
                    </a>
                    <form action="{{ route('nomina.nominas.aprobar', $nomina) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('¿Aprobar esta nómina?')" 
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white dark:text-white dark:hover:bg-green-800 rounded-lg transition">
                            Aprobar
                        </button>
                    </form>
                </div>
                @elseif($estadoVal === 'aprobada')
                <div class="flex gap-2">
                    <form action="{{ route('nomina.nominas.pagar', $nomina) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('¿Marcar como pagada?')" 
                            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white dark:text-white dark:hover:bg-purple-800 rounded-lg transition">
                            Marcar como Pagada
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>