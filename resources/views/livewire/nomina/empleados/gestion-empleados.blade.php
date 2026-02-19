<div>
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Gestión de Empleados</h1>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Empleado
            </button>
        </div>

        {{-- Mensajes de éxito/error --}}
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filtros y búsqueda --}}
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        wire:model.debounce.300ms="search" 
                        placeholder="Buscar por nombre, documento, cargo..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select wire:model="filterEstado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="retirado">Retirado</option>
                        <option value="suspendido">Suspendido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Contrato</label>
                    <select wire:model="filterTipoContrato" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="indefinido">Indefinido</option>
                        <option value="fijo">Término Fijo</option>
                        <option value="obra_labor">Obra o Labor</option>
                        <option value="prestacion_servicios">Prestación de Servicios</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabla de empleados --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre Completo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cargo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($empleados as $empleado)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $empleado->tipo_documento }} {{ $empleado->numero_documento }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $empleado->nombre_completo }}</div>
                                <div class="text-sm text-gray-500">{{ $empleado->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $empleado->cargo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                ${{ number_format($empleado->salario_basico, 2) }}
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
                                <span class="px-2 py-1 text-xs rounded-full {{ $estadoClasses[$empleado->estado] ?? '' }}">
                                    {{ ucfirst($empleado->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $empleado->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    Editar
                                </button>
                                <button wire:click="confirmDelete({{ $empleado->id }})" class="text-red-600 hover:text-red-900">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron empleados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $empleados->links() }}
        </div>
    </div>

    {{-- Modal de formulario --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-8 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $modalTitle }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        {{-- Datos Personales --}}
                        <div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Datos Personales</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Documento *</label>
                                    <select wire:model="tipo_documento" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="PA">Pasaporte</option>
                                    </select>
                                    @error('tipo_documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Número Documento *</label>
                                    <input type="text" wire:model="numero_documento" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    @error('numero_documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Primer Nombre *</label>
                                    <input type="text" wire:model="primer_nombre" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    @error('primer_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Segundo Nombre</label>
                                    <input type="text" wire:model="segundo_nombre" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Primer Apellido *</label>
                                    <input type="text" wire:model="primer_apellido" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    @error('primer_apellido') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Segundo Apellido</label>
                                    <input type="text" wire:model="segundo_apellido" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                            </div>
                        </div>

                        {{-- Datos Laborales --}}
                        <div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-4">Datos Laborales</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Ingreso *</label>
                                    <input type="date" wire:model="fecha_ingreso" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    @error('fecha_ingreso') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cargo *</label>
                                    <input type="text" wire:model="cargo" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    @error('cargo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Salario Básico *</label>
                                    <input type="number" step="0.01" wire:model="salario_basico" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    @error('salario_basico') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                                    <select wire:model="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                        <option value="retirado">Retirado</option>
                                        <option value="suspendido">Suspendido</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showModal', false)" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal de confirmación de eliminación --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Confirmar Eliminación</h3>
                    <p class="text-sm text-gray-500 mt-2">¿Está seguro de que desea eliminar este empleado?</p>
                    <div class="flex justify-center gap-3 mt-4">
                        <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>