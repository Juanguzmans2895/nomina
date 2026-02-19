<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Usuarios
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Mensajes --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-lg flex items-center justify-between">
                    <p class="text-green-700 dark:text-green-200">{{ session('success') }}</p>
                    <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 rounded-lg flex items-center justify-between">
                    <p class="text-red-700 dark:text-red-200">{{ session('error') }}</p>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">✕</button>
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Usuarios del Sistema</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Administración de accesos y permisos</p>
                </div>
                <a href="{{ route('admin.usuarios.create') }}"
                   class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuevo Usuario
                </a>
            </div>

            {{-- Estadísticas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center gap-3">
                    <div class="bg-blue-100 dark:bg-blue-900 rounded-full p-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['total'] }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center gap-3">
                    <div class="bg-green-100 dark:bg-green-900 rounded-full p-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Activos</p>
                        <p class="text-xl font-bold text-green-600">{{ $stats['activos'] }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center gap-3">
                    <div class="bg-yellow-100 dark:bg-yellow-900 rounded-full p-2">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Inactivos</p>
                        <p class="text-xl font-bold text-yellow-600">{{ $stats['inactivos'] }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center gap-3">
                    <div class="bg-red-100 dark:bg-red-900 rounded-full p-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Admins</p>
                        <p class="text-xl font-bold text-red-600">{{ $stats['admins'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                <form method="GET" action="{{ route('admin.usuarios.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Nombre o correo..."
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rol</label>
                            <select name="role" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option value="">Todos los roles</option>
                                @foreach($roles as $key => $nombre)
                                    <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                            <select name="estado" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option value="">Todos</option>
                                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activos</option>
                                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                                <option value="eliminado" {{ request('estado') == 'eliminado' ? 'selected' : '' }}>Eliminados</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                Buscar
                            </button>
                            @if(request()->hasAny(['search','role','estado']))
                                <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition" title="Limpiar">✕</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Empleado Vinculado</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Último Acceso</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($usuarios as $usuario)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition {{ $usuario->trashed() ? 'opacity-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $usuario->avatar_url }}" alt="{{ $usuario->name }}"
                                             class="w-9 h-9 rounded-full object-cover">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $usuario->name }}
                                                @if($usuario->id === auth()->id())
                                                    <span class="ml-1 text-xs text-blue-500">(tú)</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $usuario->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $color = $usuario->role_color; @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        bg-{{ $color }}-100 text-{{ $color }}-800
                                        dark:bg-{{ $color }}-900 dark:text-{{ $color }}-200">
                                        {{ $usuario->role_nombre }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    @if($usuario->empleado)
                                        <span class="text-blue-600 dark:text-blue-400">{{ $usuario->empleado->nombre_completo }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Sin vincular</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($usuario->trashed())
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Eliminado</span>
                                    @elseif($usuario->activo)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->diffForHumans() : 'Nunca' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($usuario->trashed())
                                            {{-- Restaurar --}}
                                            <form method="POST" action="{{ route('admin.usuarios.restore', $usuario->id) }}">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400" title="Restaurar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Editar --}}
                                            <a href="{{ route('admin.usuarios.edit', $usuario) }}"
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            {{-- Activar/Desactivar --}}
                                            @if($usuario->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.usuarios.toggle', $usuario) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="{{ $usuario->activo ? 'text-yellow-600 hover:text-yellow-800 dark:text-yellow-400' : 'text-green-600 hover:text-green-800 dark:text-green-400' }}"
                                                            title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                        @if($usuario->activo)
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                </form>

                                                {{-- Eliminar --}}
                                                <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}"
                                                      onsubmit="return confirm('¿Está seguro de eliminar este usuario?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400" title="Eliminar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay usuarios</h3>
                                    <p class="mt-1 text-sm text-gray-500">Crea el primer usuario del sistema</p>
                                    <a href="{{ route('admin.usuarios.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Crear Usuario
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($usuarios->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $usuarios->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>