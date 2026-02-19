<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Centros de Costo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Gestión de Centros de Costo</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Administración de centros de costo con jerarquía</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('nomina.centros-costo.index', ['vista' => 'tabla']) }}" 
                               class="px-4 py-2 {{ request('vista', 'tabla') === 'tabla' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded-lg">
                                Vista Tabla
                            </a>
                            <a href="{{ route('nomina.centros-costo.index', ['vista' => 'arbol']) }}" 
                               class="px-4 py-2 {{ request('vista') === 'arbol' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded-lg">
                                Vista Árbol
                            </a>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                Nuevo Centro
                            </button>
                        </div>
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

                    @if(request('vista', 'tabla') === 'tabla')
                        {{-- Vista en Tabla --}}
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Código</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nivel</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Centro Padre</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        @forelse($centros ?? [] as $centro)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-gray-900 dark:text-gray-200">
                                                {{ $centro->codigo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                                {{ $centro->nombre }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                    Nivel {{ $centro->nivel ?? 1 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                {{ $centro->padre->nombre ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                                    Editar
                                                </button>
                                                <button class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                No hay centros de costo registrados
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- Vista en Árbol --}}
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <div class="space-y-2">
                                @if(isset($arbolCentros) && count($arbolCentros) > 0)
                                    @foreach($arbolCentros as $nodo)
                                        @include('nomina.partials.nodo-centro', ['nodo' => $nodo, 'nivel' => 0])
                                    @endforeach
                                @else
                                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                        No hay centros de costo para mostrar en vista de árbol
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Información adicional --}}
                    <div class="mt-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">ℹ️ Información</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Los centros de costo permiten organizar y clasificar los gastos de nómina por departamentos, proyectos o áreas específicas de la empresa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>