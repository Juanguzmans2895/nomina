<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Conceptos de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Conceptos de Nómina</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Gestión de conceptos devengados, deducidos y no imputables</p>
                        </div>
                        <a href="{{ route('nomina.conceptos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuevo Concepto
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
                        <form method="GET" action="{{ route('nomina.conceptos.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                                    <input 
                                        type="text" 
                                        name="search"
                                        value="{{ request('search') }}"
                                        placeholder="Buscar por código o nombre..."
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clasificación</label>
                                    <select name="clasificacion" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg">
                                        <option value="">Todas</option>
                                        <option value="DEVENGADO" {{ request('clasificacion') == 'DEVENGADO' ? 'selected' : '' }}>Devengados</option>
                                        <option value="DEDUCIDO" {{ request('clasificacion') == 'DEDUCIDO' ? 'selected' : '' }}>Deducidos</option>
                                        <option value="NO_IMPUTABLE" {{ request('clasificacion') == 'NO_IMPUTABLE' ? 'selected' : '' }}>No Imputables</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Tabla de conceptos --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Código</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Clasificación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse ($conceptos ?? [] as $concepto)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="font-mono font-semibold text-gray-900 dark:text-gray-200">
                                                    {{ $concepto->codigo }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                    {{ $concepto->nombre }}
                                                </div>
                                                @if($concepto->descripcion)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ Str::limit($concepto->descripcion, 50) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $clasificacionValue = is_object($concepto->clasificacion) 
                                                        ? $concepto->clasificacion->value 
                                                        : $concepto->clasificacion;
                                                @endphp
                                                
                                                @if($clasificacionValue === 'DEVENGADO')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                        Devengado
                                                    </span>
                                                @elseif($clasificacionValue === 'DEDUCIDO')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                        Deducido
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                        No Imputable
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $tipoValue = is_object($concepto->tipo) 
                                                        ? $concepto->tipo->value 
                                                        : ($concepto->tipo ?? 'N/A');
                                                @endphp
                                                
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                    {{ ucfirst(strtolower(str_replace('_', ' ', $tipoValue))) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($concepto->activo)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('nomina.conceptos.edit', $concepto) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                                    Editar
                                                </a>
                                                @if(!($concepto->sistema ?? false))
                                                    <form action="{{ route('nomina.conceptos.destroy', $concepto) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este concepto?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center">
                                                <div class="flex flex-col items-center justify-center py-8">
                                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <p class="text-lg font-medium text-gray-900 dark:text-gray-200">No se encontraron conceptos</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Ejecute el seeder para cargar conceptos de prueba</p>
                                                    <div class="mt-4 space-x-2">
                                                        <a href="{{ route('nomina.conceptos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-block">
                                                            Crear Concepto
                                                        </a>
                                                        <button onclick="if(confirm('¿Ejecutar seeder de conceptos?')) alert('Ejecute: php artisan db:seed --class=ConceptosNominaSeeder')" 
                                                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                                            Info Seeder
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    @if(isset($conceptos) && method_exists($conceptos, 'links'))
                        <div class="mt-4">
                            {{ $conceptos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>