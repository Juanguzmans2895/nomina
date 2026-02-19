<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard de Nómina</h1>
                <p class="text-gray-600 dark:text-gray-300">Resumen general del sistema - {{ now()->locale('es')->isoFormat('MMMM YYYY') }}</p>
            </div>

            {{-- Alertas --}}
            @if(isset($alertas) && is_array($alertas) && count($alertas) > 0)
                <div class="mb-6 space-y-3">
                    @foreach($alertas as $alerta)
                        <div class="border-l-4 p-4 rounded-lg {{ $alerta['tipo'] === 'error' ? 'bg-red-50 border-red-500' : ($alerta['tipo'] === 'warning' ? 'bg-yellow-50 border-yellow-500' : 'bg-blue-50 border-blue-500') }}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 {{ $alerta['tipo'] === 'error' ? 'text-red-500' : ($alerta['tipo'] === 'warning' ? 'text-yellow-500' : 'text-blue-500') }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-medium {{ $alerta['tipo'] === 'error' ? 'text-red-800' : ($alerta['tipo'] === 'warning' ? 'text-yellow-800' : 'text-blue-800') }}">
                                        {{ $alerta['titulo'] }}
                                    </h3>
                                    <p class="mt-1 text-sm {{ $alerta['tipo'] === 'error' ? 'text-red-700' : ($alerta['tipo'] === 'warning' ? 'text-yellow-700' : 'text-blue-700') }}">
                                        {{ $alerta['mensaje'] }}
                                    </p>
                                    @if(isset($alerta['accion']))
                                        <a href="{{ $alerta['accion'] }}" class="mt-2 inline-flex items-center text-sm font-medium {{ $alerta['tipo'] === 'error' ? 'text-red-600 hover:text-red-500' : ($alerta['tipo'] === 'warning' ? 'text-yellow-600 hover:text-yellow-500' : 'text-blue-600 hover:text-blue-500') }}">
                                            {{ $alerta['textoAccion'] }} →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Métricas Principales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Empleados Activos --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Empleados Activos</p>
                            <h3 class="text-3xl font-bold text-blue-600">{{ number_format($totalEmpleados ?? 0) }}</h3>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="mr-2">Activos: {{ $empleadosActivos ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900 dark:bg-gray-700 rounded-full p-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Nómina del Mes --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Nómina del Mes</p>
                            <h3 class="text-2xl font-bold text-green-600">
                                @if(isset($metricas['nomina_mes_actual']['total_neto']) && $metricas['nomina_mes_actual']['total_neto'] > 0)
                                    ${{ number_format($metricas['nomina_mes_actual']['total_neto'] / 1000000, 1) }}M
                                @else
                                    $0M
                                @endif
                            </h3>
                            @if(isset($metricas['nomina_mes_actual']['existe']) && $metricas['nomina_mes_actual']['existe'])
                                @php
                                    // Manejar Enum o string
                                    $estadoNomina = $metricas['nomina_mes_actual']['estado'] ?? 'pendiente';
                                    $estadoValue = is_object($estadoNomina) ? $estadoNomina->value : $estadoNomina;
                                    $estadoTexto = ucfirst($estadoValue);
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full mt-2 inline-block {{ $estadoValue === 'pagada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $estadoTexto }}
                                </span>
                            @endif
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Contratos Activos --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Contratos Activos</p>
                            <h3 class="text-3xl font-bold text-purple-600">{{ $contratosActivos ?? 0 }}</h3>
                            @if(isset($contratosProximosVencer) && $contratosProximosVencer > 0)
                                <p class="mt-2 text-xs text-orange-600">⚠️ {{ $contratosProximosVencer }} por vencer</p>
                            @endif
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Provisiones --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Provisiones</p>
                            <h3 class="text-2xl font-bold text-indigo-600">
                                @if(isset($metricas['provisiones_totales']) && $metricas['provisiones_totales'] > 0)
                                    ${{ number_format($metricas['provisiones_totales'] / 1000000, 1) }}M
                                @else
                                    $0M
                                @endif
                            </h3>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Total acumulado</p>
                        </div>
                        <div class="bg-indigo-100 dark:bg-indigo-900 rounded-full p-3">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones Rápidas --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Acciones Rápidas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('nomina.nominas.liquidar') }}" class="flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Liquidar Nómina
                    </a>

                    <a href="{{ route('nomina.empleados.index') }}" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-black dark:text-white">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Empleados
                    </a>

                    <a href="{{ route('nomina.novedades.index') }}" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-black dark:text-white">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Novedades
                    </a>

                    <a href="{{ route('nomina.reportes.index') }}" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-black dark:text-white">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Reportes
                    </a>
                </div>
            </div>

            {{-- Nóminas Recientes --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Nóminas Recientes</h3>
                    <a href="{{ route('nomina.nominas.historial') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:hover:text-blue-400">Ver todas →</a>
                </div>

                @if(isset($nominasRecientes) && count($nominasRecientes) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Período</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($nominasRecientes as $nomina)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 dark:hover:bg-opacity-50 transition">
                                    <td class="px-6 py-4 text-sm text-black dark:text-white font-mono">{{ $nomina['numero'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-black dark:text-white">{{ $nomina['periodo'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $estadoNomina = $nomina['estado'] ?? 'N/A';
                                            $estadoValue = is_object($estadoNomina) ? $estadoNomina->value : $estadoNomina;
                                            $estadoTexto = is_string($estadoValue) ? ucfirst($estadoValue) : 'N/A';
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            {{ $estadoTexto }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-black dark:text-white font-mono">
                                        ${{ number_format($nomina['total_neto'] ?? 0, 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay nóminas</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza liquidando tu primera nómina</p>
                        <div class="mt-6">
                            <a href="{{ route('nomina.nominas.liquidar') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Liquidar Nómina
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>