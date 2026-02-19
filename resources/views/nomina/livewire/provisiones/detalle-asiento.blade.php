<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalle de Asiento Contable
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Información del Asiento --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6 lg:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $asiento->numero_asiento }}</h3>
                        <span class="px-3 py-1 text-sm rounded-full 
                            {{ $asiento->estado === 'contabilizado' ? 'bg-green-100 text-green-800' : 
                               ($asiento->estado === 'aprobado' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($asiento->estado) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fecha</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $asiento->fecha_asiento->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tipo</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $asiento->tipo_asiento)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Descripción</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $asiento->descripcion }}</p>
                        </div>
                    </div>

                    {{-- Totales --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 border-t dark:border-gray-700 pt-4">
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Débitos</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                ${{ number_format($asiento->total_debitos, 0) }}
                            </p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Créditos</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                ${{ number_format($asiento->total_creditos, 0) }}
                            </p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Diferencia</p>
                            <p class="text-2xl font-bold {{ abs($asiento->total_debitos - $asiento->total_creditos) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format(abs($asiento->total_debitos - $asiento->total_creditos), 0) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detalles del Asiento --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Movimientos Contables</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cuenta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tercero</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Débito</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crédito</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($detalles as $detalle)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                        {{ $detalle['cuenta'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                        {{ $detalle['tercero'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-mono {{ $detalle['debito'] > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}">
                                        {{ $detalle['debito'] > 0 ? '$' . number_format($detalle['debito'], 0) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-mono {{ $detalle['credito'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                                        {{ $detalle['credito'] > 0 ? '$' . number_format($detalle['credito'], 0) : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700 font-bold">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-200">
                                        TOTALES:
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-mono text-blue-600 dark:text-blue-400">
                                        ${{ number_format($asiento->total_debitos, 0) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-mono text-green-600 dark:text-green-400">
                                        ${{ number_format($asiento->total_creditos, 0) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('nomina.asientos.index') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
                            Volver
                        </a>
                        
                        @if($asiento->estado === 'borrador')
                        <div class="space-x-2">
                            <button onclick="if(confirm('¿Aprobar este asiento?')) window.location.href='{{ route('nomina.asientos.aprobar', $asiento->id) }}'" 
                                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                Aprobar
                            </button>
                        </div>
                        @endif
                        
                        @if($asiento->estado === 'aprobado')
                        <div class="space-x-2">
                            <button onclick="if(confirm('¿Contabilizar este asiento?')) window.location.href='{{ route('nomina.asientos.contabilizar', $asiento->id) }}'" 
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                Contabilizar
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>