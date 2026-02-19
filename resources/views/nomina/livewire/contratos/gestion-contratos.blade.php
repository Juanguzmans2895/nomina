<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Contratos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Gestión de Contratos</h1>
                        <a href="{{ route('nomina.contratos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-block">
                            Nuevo Contrato
                        </a>
                    </div>

                    {{-- Mensajes --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Filtros --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4 mb-6">
                        <form method="GET" action="{{ route('nomina.contratos.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Buscar..." 
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                >
                                <select 
                                    name="estado" 
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                                    <option value="aprobado" {{ request('estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="en_ejecucion" {{ request('estado') == 'en_ejecucion' ? 'selected' : '' }}>En Ejecución</option>
                                    <option value="terminado" {{ request('estado') == 'terminado' ? 'selected' : '' }}>Terminado</option>
                                </select>
                                <input 
                                    type="date" 
                                    name="fecha_inicio" 
                                    value="{{ request('fecha_inicio') }}"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg"
                                >
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tabla de Contratos --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Número</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contratista</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fechas</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Total</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Saldo</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse($contratos ?? [] as $contrato)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-gray-900 dark:text-gray-200">
                                            {{ $contrato->numero_contrato }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-200">{{ $contrato->nombre_contratista ?? $contrato->contratista }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $contrato->numero_documento_contratista ?? 'N/A' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            <p>{{ $contrato->fecha_inicio ? $contrato->fecha_inicio->format('d/m/Y') : 'N/A' }}</p>
                                            <p class="text-gray-500 dark:text-gray-400">{{ $contrato->fecha_fin ? $contrato->fecha_fin->format('d/m/Y') : 'N/A' }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900 dark:text-gray-200">
                                            ${{ number_format($contrato->valor_total ?? 0, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900 dark:text-gray-200">
                                            ${{ number_format($contrato->saldo_pendiente ?? 0, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $estadoClasses = [
                                                    'en_ejecucion' => 'bg-green-100 text-green-800',
                                                    'aprobado' => 'bg-blue-100 text-blue-800',
                                                    'borrador' => 'bg-yellow-100 text-yellow-800',
                                                    'terminado' => 'bg-gray-100 text-gray-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full {{ $estadoClasses[$contrato->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $contrato->estado)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-2">
                                                Editar
                                            </button>
                                            <a href="{{ route('nomina.contratos.pagos', $contrato->id) }}" class="text-green-600 hover:text-green-900 dark:text-green-400">
                                                Pagos
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron contratos
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    @if(isset($contratos) && $contratos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $contratos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>