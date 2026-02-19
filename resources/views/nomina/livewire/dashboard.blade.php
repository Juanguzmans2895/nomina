<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header con fecha y hora actual --}}
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Dashboard de Nómina</h1>
                    <p class="text-gray-600 dark:text-gray-400">Resumen ejecutivo del sistema</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ now()->translatedFormat('l, d \de F \de Y') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">Actualizado: {{ now()->format('h:i A') }}</p>
                </div>
            </div>

            {{-- Métricas principales (8 tarjetas) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                
                {{-- 1. Empleados Activos --}}
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">{{ $metricas['empleados_activos']['total'] ?? 0 }}</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Empleados Activos</p>
                    <div class="flex gap-3 text-xs opacity-90">
                        <span>Indefinido: {{ $metricas['empleados_activos']['indefinidos'] ?? 0 }}</span>
                        <span>•</span>
                        <span>Fijo: {{ $metricas['empleados_activos']['fijos'] ?? 0 }}</span>
                    </div>
                </div>

                {{-- 2. Nómina Actual --}}
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">${{ number_format(($metricas['nomina_mes_actual']['total_neto'] ?? 0) / 1000000, 1) }}M</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Nómina Mes Actual</p>
                    <div class="flex gap-2 text-xs">
                        @if($metricas['nomina_mes_actual']['existe'] ?? false)
                            <span class="px-2 py-0.5 bg-white/20 rounded-full">{{ ucfirst($metricas['nomina_mes_actual']['estado'] ?? '') }}</span>
                            @if(($metricas['nomina_mes_actual']['tendencia'] ?? 0) != 0)
                                <span class="opacity-90">
                                    {{ $metricas['nomina_mes_actual']['tendencia'] > 0 ? '↑' : '↓' }} {{ abs($metricas['nomina_mes_actual']['tendencia']) }}%
                                </span>
                            @endif
                        @else
                            <span class="opacity-75">Sin procesar</span>
                        @endif
                    </div>
                </div>

                {{-- 3. Provisiones Totales --}}
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">${{ number_format(($metricas['provisiones_totales'] ?? 0) / 1000000, 1) }}M</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Provisiones Acumuladas</p>
                    <p class="text-xs opacity-90">Cesantías • Prima • Vacaciones</p>
                </div>

                {{-- 4. Contratos Activos --}}
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">{{ $metricas['contratos']['activos'] ?? 0 }}</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Contratos Activos</p>
                    @if(($metricas['contratos']['proximos_vencer'] ?? 0) > 0)
                        <p class="text-xs opacity-90">⚠️ {{ $metricas['contratos']['proximos_vencer'] }} por vencer</p>
                    @else
                        <p class="text-xs opacity-75">Todos vigentes</p>
                    @endif
                </div>

                {{-- 5. Asientos Contables --}}
                <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">{{ $metricas['asientos_contables']['total'] ?? 0 }}</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Asientos Contables</p>
                    <p class="text-xs opacity-90">Contabilizados: {{ $metricas['asientos_contables']['contabilizados'] ?? 0 }}</p>
                </div>

                {{-- 6. Novedades Pendientes --}}
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">{{ $metricas['novedades_pendientes'] ?? 0 }}</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Novedades Pendientes</p>
                    <p class="text-xs opacity-90">Por aplicar en nómina</p>
                </div>

                {{-- 7. Centros de Costo --}}
                <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">{{ $metricas['centros_costo']['total'] ?? 0 }}</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Centros de Costo</p>
                    <p class="text-xs opacity-90">Activos: {{ $metricas['centros_costo']['activos'] ?? 0 }}</p>
                </div>

                {{-- 8. Conceptos de Nómina --}}
                <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold">{{ $metricas['conceptos']['total'] ?? 0 }}</h3>
                    </div>
                    <p class="text-sm font-medium mb-2">Conceptos de Nómina</p>
                    <div class="flex gap-2 text-xs opacity-90">
                        <span>Dev: {{ $metricas['conceptos']['devengos'] ?? 0 }}</span>
                        <span>•</span>
                        <span>Ded: {{ $metricas['conceptos']['deducciones'] ?? 0 }}</span>
                    </div>
                </div>

            </div>

            {{-- Sección de Gráficas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                {{-- Evolución de Nómina (últimos 6 meses) --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Evolución de Nómina</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Últimos 6 meses</span>
                    </div>
                    <div class="h-64">
                        <canvas id="evolucionChart"></canvas>
                    </div>
                </div>

                {{-- Distribución por Tipo de Contrato --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Distribución de Empleados</h3>
                    <div class="h-64">
                        <canvas id="tipoContratoChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- Sección de Tablas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                
                {{-- Nóminas Recientes --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Nóminas Recientes</h3>
                        <a href="{{ route('nomina.nominas.historial') }}" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">Ver todas →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Número</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Período</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Estado</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300">Total Neto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($nominasRecientes ?? [] as $nomina)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">{{ $nomina['numero'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $nomina['periodo'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $nomina['estado'] === 'pagada' ? 'bg-green-100 text-green-800' : 
                                               ($nomina['estado'] === 'aprobada' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($nomina['estado']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-200">
                                        ${{ number_format($nomina['total_neto'], 0) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                        No hay nóminas registradas
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Novedades Pendientes --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Novedades Pendientes</h3>
                        @if(($metricas['novedades_pendientes'] ?? 0) > 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ $metricas['novedades_pendientes'] }}
                            </span>
                        @endif
                    </div>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @forelse($novedadesPendientes ?? [] as $novedad)
                        <div class="border-l-4 border-orange-500 dark:border-orange-400 bg-orange-50 dark:bg-orange-900/20 pl-4 py-3 rounded-r">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $novedad['empleado'] }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $novedad['concepto'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $novedad['fecha'] }}</p>
                                </div>
                                <span class="text-sm font-mono font-semibold text-orange-600 dark:text-orange-400">
                                    ${{ number_format($novedad['valor'], 0) }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                            ✓ No hay novedades pendientes
                        </p>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Sección de Estado del Sistema --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                {{-- Provisiones Detalladas --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Provisiones Detalladas</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Cesantías</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Acumuladas</p>
                            </div>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                ${{ number_format(($metricas['provisiones_detalle']['cesantias'] ?? 0) / 1000000, 1) }}M
                            </p>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Intereses</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sobre cesantías</p>
                            </div>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                ${{ number_format(($metricas['provisiones_detalle']['intereses'] ?? 0) / 1000000, 1) }}M
                            </p>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Prima</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Servicios</p>
                            </div>
                            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                ${{ number_format(($metricas['provisiones_detalle']['prima'] ?? 0) / 1000000, 1) }}M
                            </p>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Vacaciones</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Acumuladas</p>
                            </div>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                ${{ number_format(($metricas['provisiones_detalle']['vacaciones'] ?? 0) / 1000000, 1) }}M
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Alertas y Notificaciones --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Alertas del Sistema</h3>
                    <div class="space-y-3">
                        @if(($metricas['contratos']['proximos_vencer'] ?? 0) > 0)
                        <div class="flex items-start gap-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-l-4 border-yellow-500">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Contratos por Vencer</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $metricas['contratos']['proximos_vencer'] }} contratos vencen en 30 días</p>
                            </div>
                        </div>
                        @endif

                        @if(($metricas['novedades_pendientes'] ?? 0) > 0)
                        <div class="flex items-start gap-3 p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg border-l-4 border-orange-500">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Novedades Pendientes</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $metricas['novedades_pendientes'] }} novedades sin aplicar</p>
                            </div>
                        </div>
                        @endif

                        @if(($metricas['asientos_contables']['descuadrados'] ?? 0) > 0)
                        <div class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border-l-4 border-red-500">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Asientos Descuadrados</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $metricas['asientos_contables']['descuadrados'] }} asientos requieren revisión</p>
                            </div>
                        </div>
                        @endif

                        @if(($metricas['contratos']['proximos_vencer'] ?? 0) == 0 && ($metricas['novedades_pendientes'] ?? 0) == 0 && ($metricas['asientos_contables']['descuadrados'] ?? 0) == 0)
                        <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border-l-4 border-green-500">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Sistema Operando Normalmente</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">No hay alertas pendientes</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Accesos Rápidos --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Accesos Rápidos</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('nomina.nominas.liquidar') }}" class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Nueva Nómina</span>
                        </a>

                        <a href="{{ route('nomina.empleados.index') }}" class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Empleados</span>
                        </a>

                        <a href="{{ route('nomina.novedades.index') }}" class="flex flex-col items-center p-4 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/30 rounded-lg transition">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Novedades</span>
                        </a>

                        <a href="{{ route('nomina.reportes.index') }}" class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg transition">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Reportes</span>
                        </a>

                        <a href="{{ route('nomina.provisiones.index') }}" class="flex flex-col items-center p-4 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 rounded-lg transition">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Provisiones</span>
                        </a>

                        <a href="{{ route('nomina.asientos.index') }}" class="flex flex-col items-center p-4 bg-cyan-50 dark:bg-cyan-900/20 hover:bg-cyan-100 dark:hover:bg-cyan-900/30 rounded-lg transition">
                            <svg class="w-8 h-8 text-cyan-600 dark:text-cyan-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">Asientos</span>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Contratos Próximos a Vencer --}}
            @if(count($contratosProximosVencer ?? []) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    ⚠️ Contratos Próximos a Vencer (30 días)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Número</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Contratista</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Vencimiento</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300">Días Restantes</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300">Saldo Pendiente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($contratosProximosVencer as $contrato)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">{{ $contrato['numero'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $contrato['contratista'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $contrato['fecha_fin'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $contrato['dias_restantes'] <= 15 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                           'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                        {{ $contrato['dias_restantes'] }} días
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-200">
                                    ${{ number_format($contrato['saldo_pendiente'], 0) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Configuración común para modo oscuro
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#e5e7eb' : '#374151';
        const gridColor = isDark ? '#4b5563' : '#e5e7eb';

        // Gráfica de Evolución de Nómina
        const evolucionData = @json($evolucionNomina ?? []);
        const ctxEvolucion = document.getElementById('evolucionChart')?.getContext('2d');
        
        if (ctxEvolucion && evolucionData.length > 0) {
            new Chart(ctxEvolucion, {
                type: 'line',
                data: {
                    labels: evolucionData.map(item => item.mes_nombre),
                    datasets: [{
                        label: 'Total Neto',
                        data: evolucionData.map(item => item.total_neto),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + (context.parsed.y / 1000000).toFixed(2) + 'M';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return '$' + (value / 1000000).toFixed(1) + 'M';
                                },
                                color: textColor
                            },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    }
                }
            });
        }

        // Gráfica por Tipo de Contrato
        const tipoContratoData = @json($distribucionEmpleados['por_tipo_contrato'] ?? []);
        const ctxTipo = document.getElementById('tipoContratoChart')?.getContext('2d');
        
        if (ctxTipo && tipoContratoData.length > 0) {
            new Chart(ctxTipo, {
                type: 'doughnut',
                data: {
                    labels: tipoContratoData.map(item => item.tipo_contrato),
                    datasets: [{
                        data: tipoContratoData.map(item => item.total),
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(139, 92, 246)',
                            'rgb(236, 72, 153)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>