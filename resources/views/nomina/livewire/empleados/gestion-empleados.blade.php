<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Empleados
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Gestión de Empleados</h1>
                        <a href="{{ route('nomina.empleados.crear') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuevo Empleado
                        </a>
                    </div>

                    {{-- Mensajes de éxito/error --}}
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

                    {{-- Filtros y búsqueda --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-4 mb-6">
                        <form method="GET" action="{{ route('nomina.empleados.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                                    <input 
                                        type="text" 
                                        name="search"
                                        value="{{ request('search') }}"
                                        placeholder="Buscar por nombre, documento, cargo..."
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                                    <select name="estado" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">Todos</option>
                                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        <option value="retirado" {{ request('estado') == 'retirado' ? 'selected' : '' }}>Retirado</option>
                                        <option value="suspendido" {{ request('estado') == 'suspendido' ? 'selected' : '' }}>Suspendido</option>
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

                    {{-- Tabla de empleados --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Documento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre Completo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cargo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Salario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse ($empleados ?? [] as $empleado)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                {{ $empleado->tipo_documento }} {{ $empleado->numero_documento }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">{{ $empleado->nombre_completo }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $empleado->email ?? 'Sin email' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                {{ $empleado->cargo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-200">
                                                ${{ number_format($empleado->salario_basico, 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $estadoClasses = [
                                                        'activo' => 'bg-green-100 text-green-800',
                                                        'inactivo' => 'bg-gray-100 text-gray-800',
                                                        'retirado' => 'bg-red-100 text-red-800',
                                                        'suspendido' => 'bg-yellow-100 text-yellow-800',
                                                    ];
                                                @endphp
                                                <span class="px-2 py-1 text-xs rounded-full {{ $estadoClasses[$empleado->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($empleado->estado) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('nomina.empleados.editar', $empleado->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                                    Editar
                                                </a>
                                                <form action="{{ route('nomina.empleados.destroy', $empleado->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este empleado?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                No se encontraron empleados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    @if(isset($empleados) && $empleados instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $empleados->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>