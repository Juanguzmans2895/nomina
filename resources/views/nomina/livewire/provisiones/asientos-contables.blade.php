<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Asientos Contables de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Asientos Contables de Nómina</h1>
                        <div class="flex gap-3">
                            <a href="{{ route('nomina.asientos.exportar') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                Exportar Excel
                            </a>
                        </div>
                    </div>

                    {{-- Filtros --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4 mb-6">
                        <form method="GET" action="{{ route('nomina.asientos.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." 
                                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                
                                <select name="tipo" class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                    <option value="">Todos los tipos</option>
                                    <option value="causacion_nomina" {{ request('tipo') == 'causacion_nomina' ? 'selected' : '' }}>Causación Nómina</option>
                                    <option value="pago_nomina" {{ request('tipo') == 'pago_nomina' ? 'selected' : '' }}>Pago Nómina</option>
                                    <option value="provision_mensual" {{ request('tipo') == 'provision_mensual' ? 'selected' : '' }}>Provisión Mensual</option>
                                    <option value="pago_provision" {{ request('tipo') == 'pago_provision' ? 'selected' : '' }}>Pago Provisión</option>
                                </select>
                                
                                <select name="estado" class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                    <option value="">Todos los estados</option>
                                    <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                                    <option value="aprobado" {{ request('estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="contabilizado" {{ request('estado') == 'contabilizado' ? 'selected' : '' }}>Contabilizado</option>
                                    <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulado</option>
                                </select>
                                
                                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" 
                                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                
                                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" 
                                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Estadísticas --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Asientos</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $estadisticas['total'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Borrador</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $estadisticas['borrador'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Aprobados</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $estadisticas['aprobados'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Contabilizados</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $estadisticas['contabilizados'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Anulados</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $estadisticas['anulados'] ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Tabla --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Número</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Débitos</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Créditos</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse($asientos ?? [] as $asiento)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-6 py-4 font-mono text-gray-900 dark:text-gray-200">{{ $asiento->numero_asiento }}</td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-gray-200">{{ $asiento->fecha_asiento->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst(str_replace('_', ' ', $asiento->tipo_asiento)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono text-gray-900 dark:text-gray-200">${{ number_format($asiento->total_debitos, 0) }}</td>
                                        <td class="px-6 py-4 text-right font-mono text-gray-900 dark:text-gray-200">${{ number_format($asiento->total_creditos, 0) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $asiento->estado === 'contabilizado' ? 'bg-green-100 text-green-800' : 
                                                ($asiento->estado === 'aprobado' ? 'bg-blue-100 text-blue-800' : 
                                                ($asiento->estado === 'anulado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                                {{ ucfirst($asiento->estado) }}
                                            </span>
                                            @if(abs($asiento->total_debitos - $asiento->total_creditos) > 0.01)
                                                <span class="ml-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                    ⚠️ Descuadrado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm space-x-2">
                                            <a href="{{ route('nomina.asientos.detalle', $asiento->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron asientos contables
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    @if(isset($asientos) && $asientos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $asientos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>