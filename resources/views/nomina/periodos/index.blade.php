<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestión de Períodos
            </h2>
            <a href="{{ route('nomina.periodos.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                Crear Nuevo Período
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Mensajes -->
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $message }}
                </div>
            @endif

            @if ($message = Session::get('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $message }}
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Estado
                        </label>
                        <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">Todos</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                                    {{ ucfirst($estado) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo de Nómina
                        </label>
                        <select name="tipo_nomina_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">Todos</option>
                            @foreach($tiposNomina as $tipo)
                                <option value="{{ $tipo->id }}" {{ request('tipo_nomina_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Año
                        </label>
                        <select name="anio" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">Todos</option>
                            @for($year = now()->year; $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ request('anio') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Períodos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Período
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Rango de Fechas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tipo de Nómina
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($periodos as $periodo)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $periodo->nombre ?? $periodo->codigo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $periodo->fecha_inicio->format('d/m/Y') }} - {{ $periodo->fecha_fin->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($periodo->estado === 'abierto')
                                            bg-green-100 text-green-800
                                        @elseif($periodo->estado === 'cerrado')
                                            bg-red-100 text-red-800
                                        @else
                                            bg-gray-100 text-gray-800
                                        @endif
                                    ">
                                        {{ ucfirst($periodo->estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $periodo->tipoNomina->nombre ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('nomina.periodos.edit', $periodo) }}" class="text-blue-600 hover:text-blue-900">
                                        Editar
                                    </a>
                                    
                                    @if($periodo->estado === 'abierto')
                                        <form action="{{ route('nomina.periodos.cerrar', $periodo) }}" method="POST" class="inline" onsubmit="return confirm('¿Cerrar este período?');">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                                Cerrar
                                            </button>
                                        </form>
                                    @elseif($periodo->estado === 'cerrado')
                                        <form action="{{ route('nomina.periodos.reabrir', $periodo) }}" method="POST" class="inline" onsubmit="return confirm('¿Reabrir este período?');">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="text-purple-600 hover:text-purple-900">
                                                Reabrir
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(!$periodo->nominas()->exists())
                                        <form action="{{ route('nomina.periodos.destroy', $periodo) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este período?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No hay períodos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $periodos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
