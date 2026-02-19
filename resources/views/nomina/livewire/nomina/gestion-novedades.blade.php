<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de novedades de nómina
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 p-4 rounded-lg">
                    <p class="text-green-700 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-400 p-4 rounded-lg">
                    <p class="text-red-700 dark:text-red-200">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Gestión de Novedades</h1>
                    <p class="text-gray-600 mt-1 dark:text-gray-400">Administración de novedades de nómina</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="{{ route('nomina.novedades.importar') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Importar Novedades
                    </a>
                    <a href="{{ route('nomina.novedades.crear') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nueva Novedad
                    </a>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <form method="GET" action="{{ route('nomina.novedades.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Empleado</label>
                            <input type="text" 
                                name="empleado" 
                                value="{{ request('empleado') }}" 
                                placeholder="Buscar empleado..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Concepto</label>
                            <input type="text" 
                                name="concepto" 
                                value="{{ request('concepto') }}" 
                                placeholder="Código de concepto..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                            <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="procesada" {{ request('estado') == 'procesada' ? 'selected' : '' }}>Procesada</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Período</label>
                            <select name="periodo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">Todos</option>
                                <option value="actual" {{ request('periodo') == 'actual' ? 'selected' : '' }}>Período actual</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                Buscar
                            </button>
                            @if(request()->hasAny(['empleado', 'concepto', 'estado', 'periodo']))
                                <a href="{{ route('nomina.novedades.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition" title="Limpiar filtros">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Novedades</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $novedades->total() ?? 0 }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ isset($novedades) ? $novedades->where('procesada', false)->count() : 0 }}
                            </p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Procesadas</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ isset($novedades) ? $novedades->where('procesada', true)->count() : 0 }}
                            </p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de Novedades --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Empleado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Concepto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Cantidad
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Valor Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($novedades ?? [] as $novedad)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                {{ $novedad->empleado->nombre_completo ?? 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $novedad->empleado->numero_documento ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">{{ $novedad->concepto->nombre ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $novedad->concepto->codigo ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $novedad->fecha_novedad ? $novedad->fecha_novedad->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200 text-right font-mono">
                                    {{ number_format($novedad->cantidad ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200 text-right font-mono font-bold">
                                    ${{ number_format($novedad->valor_total ?? 0, 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($novedad->procesada ?? false)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Procesada
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('nomina.novedades.editar', $novedad->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition" 
                                           title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('nomina.novedades.destroy', $novedad->id) }}" 
                                              onsubmit="return confirm('¿Está seguro de eliminar esta novedad?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition" 
                                                    title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12">
                                    <div class="text-center bg-white dark:bg-gray-800 rounded-lg p-6">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay novedades</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza creando una nueva novedad o importándolas</p>
                                        <div class="mt-6 flex justify-center gap-3">
                                            <a href="{{ route('nomina.novedades.crear') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                Nueva Novedad
                                            </a>
                                            <a href="{{ route('nomina.novedades.importar') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                Importar
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($novedades->isNotEmpty())
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th colspan="4" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-300 tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-900 dark:text-gray-200 tracking-wider">
                                    ${{ number_format($novedades->sum('valor_total') ?? 0, 0) }}
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Paginación --}}
                @if(isset($novedades) && method_exists($novedades, 'links'))
                    <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        {{ $novedades->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>