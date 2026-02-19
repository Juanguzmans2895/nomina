<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Consulta de Provisiones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Consulta de Provisiones</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Saldos de cesantías, intereses, prima y vacaciones por empleado</p>
                    </div>

                    {{-- Filtros --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4 mb-6">
                        <form method="GET" action="{{ route('nomina.provisiones.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <input 
                                        type="text" 
                                        name="search"
                                        value="{{ request('search') }}"
                                        placeholder="Buscar por empleado o documento..."
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <select name="estado" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                        <option value="">Todos los estados</option>
                                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activos</option>
                                        <option value="retirado" {{ request('estado') == 'retirado' ? 'selected' : '' }}>Retirados</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Resumen General --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Cesantías</p>
                            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                ${{ number_format(($totales['cesantias'] ?? 0) / 1000000, 1) }}M
                            </h3>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Intereses Cesantías</p>
                            <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">
                                ${{ number_format(($totales['intereses'] ?? 0) / 1000000, 1) }}M
                            </h3>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Prima de Servicios</p>
                            <h3 class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                ${{ number_format(($totales['prima'] ?? 0) / 1000000, 1) }}M
                            </h3>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Vacaciones</p>
                            <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                ${{ number_format(($totales['vacaciones'] ?? 0) / 1000000, 1) }}M
                            </h3>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total General</p>
                            <h3 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                ${{ number_format(($totales['total'] ?? 0) / 1000000, 1) }}M
                            </h3>
                        </div>
                    </div>

                    {{-- Botones de Acciones --}}
                    <div class="flex gap-3 mb-6">
                        <a href="{{ route('nomina.reportes.excel-provisiones') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Exportar Excel
                        </a>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Generar PDF
                        </button>
                    </div>

                    {{-- Tabla de Provisiones --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Empleado</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Antigüedad</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Salario</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cesantías</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Intereses</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prima</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vacaciones</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse($provisiones ?? [] as $provision)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-gray-200">{{ $provision->empleado->nombre_completo ?? 'N/A' }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $provision->empleado->numero_documento ?? 'N/A' }}</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-gray-200">
                                                <div>
                                                    <p class="font-semibold">{{ $provision->antiguedad_anos ?? 0 }} años</p>
                                                    <p class="text-gray-500 dark:text-gray-400 text-xs">{{ $provision->antiguedad_meses ?? 0 }} meses</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-sm text-gray-900 dark:text-gray-200">
                                                ${{ number_format($provision->salario_base ?? 0, 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-sm">
                                                <p class="font-semibold text-blue-600 dark:text-blue-400">
                                                    ${{ number_format($provision->saldo_cesantias ?? 0, 0) }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-sm">
                                                <p class="font-semibold text-green-600 dark:text-green-400">
                                                    ${{ number_format($provision->saldo_intereses ?? 0, 0) }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-sm">
                                                <p class="font-semibold text-purple-600 dark:text-purple-400">
                                                    ${{ number_format($provision->saldo_prima ?? 0, 0) }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-sm">
                                                <p class="font-semibold text-orange-600 dark:text-orange-400">
                                                    ${{ number_format($provision->saldo_vacaciones ?? 0, 0) }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono font-bold text-lg text-indigo-600 dark:text-indigo-400">
                                                ${{ number_format(($provision->saldo_cesantias ?? 0) + ($provision->saldo_intereses ?? 0) + ($provision->saldo_prima ?? 0) + ($provision->saldo_vacaciones ?? 0), 0) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                No se encontraron provisiones
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(isset($provisiones) && $provisiones->count() > 0)
                                    <tfoot class="bg-gray-50 dark:bg-gray-700 font-bold">
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-right text-gray-900 dark:text-gray-200">TOTALES:</td>
                                            <td class="px-6 py-4 text-right font-mono text-blue-600 dark:text-blue-400">
                                                ${{ number_format($totales['cesantias'] ?? 0, 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-green-600 dark:text-green-400">
                                                ${{ number_format($totales['intereses'] ?? 0, 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-purple-600 dark:text-purple-400">
                                                ${{ number_format($totales['prima'] ?? 0, 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-orange-600 dark:text-orange-400">
                                                ${{ number_format($totales['vacaciones'] ?? 0, 0) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-lg text-indigo-600 dark:text-indigo-400">
                                                ${{ number_format($totales['total'] ?? 0, 0) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Información --}}
                    <div class="mt-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">ℹ️ Información sobre Provisiones</h4>
                        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1">
                            <li>• <strong>Cesantías:</strong> 8.33% del salario mensual causado</li>
                            <li>• <strong>Intereses:</strong> 12% anual sobre el saldo de cesantías</li>
                            <li>• <strong>Prima:</strong> 8.33% del salario mensual (pago semestral)</li>
                            <li>• <strong>Vacaciones:</strong> 4.17% del salario mensual</li>
                            <li>• Los saldos mostrados incluyen causación acumulada menos pagos realizados</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>