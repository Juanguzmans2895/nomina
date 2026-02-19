<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Historial de Nóminas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg text-black dark:text-gray-200 font-semibold mb-4">Listado de Nóminas Procesadas</h3>
                
                @if($nominas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Período</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @foreach($nominas as $nomina)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-black dark:text-white text-sm">{{ $nomina->numero_nomina }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-black dark:text-white text-sm">{{ $nomina->tipo->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-black dark:text-white text-sm">{{ $nomina->periodo->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $nomina->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-black dark:text-white text-sm text-right">${{ number_format($nomina->total_neto, 0) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('nomina.nominas.detalles', $nomina->id) }}" class="text-indigo-600 hover:text-indigo-900">Ver detalles</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $nominas->links() }}
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay nóminas registradas</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>