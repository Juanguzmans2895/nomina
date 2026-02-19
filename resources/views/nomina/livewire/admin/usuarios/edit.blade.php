<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($usuario) ? 'Editar Usuario' : 'Nuevo Usuario' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">

                {{-- Header del formulario --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-5">
                    <div class="flex items-center gap-4">
                        @if(isset($usuario))
                            <img src="{{ $usuario->avatar_url }}" alt="{{ $usuario->name }}"
                                 class="w-16 h-16 rounded-full border-4 border-white/30 object-cover">
                        @else
                            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h2 class="text-xl font-bold text-white">
                                {{ isset($usuario) ? $usuario->name : 'Nuevo Usuario' }}
                            </h2>
                            <p class="text-blue-200 text-sm">
                                {{ isset($usuario) ? $usuario->email : 'Completa la información del usuario' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Errores --}}
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ isset($usuario) ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}">
                        @csrf
                        @if(isset($usuario)) @method('PUT') @endif

                        <div class="space-y-6">

                            {{-- SECCIÓN 1: Información Personal --}}
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                                    Información Personal
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Nombre Completo *
                                        </label>
                                        <input type="text" name="name"
                                               value="{{ old('name', $usuario->name ?? '') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Ej: Juan Carlos Pérez García">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Correo Electrónico *
                                        </label>
                                        <input type="email" name="email"
                                               value="{{ old('email', $usuario->email ?? '') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="usuario@empresa.com">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Rol *
                                        </label>
                                        <select name="role" required
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccione un rol...</option>
                                            @foreach($roles as $key => $nombre)
                                                <option value="{{ $key }}"
                                                        {{ old('role', $usuario->role ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700">

                            {{-- SECCIÓN 2: Contraseña --}}
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                                    {{ isset($usuario) ? 'Cambiar Contraseña' : 'Contraseña' }}
                                </h3>
                                @if(isset($usuario))
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        Deja en blanco para mantener la contraseña actual
                                    </p>
                                @endif
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Contraseña {{ !isset($usuario) ? '*' : '' }}
                                        </label>
                                        <input type="password" name="password"
                                               {{ !isset($usuario) ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Mínimo 8 caracteres">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Confirmar Contraseña {{ !isset($usuario) ? '*' : '' }}
                                        </label>
                                        <input type="password" name="password_confirmation"
                                               {{ !isset($usuario) ? 'required' : '' }}
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Repite la contraseña">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700">

                            {{-- SECCIÓN 3: Configuración --}}
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                                    Configuración
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Empleado Vinculado (opcional)
                                        </label>
                                        <select name="empleado_id"
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Sin vincular</option>
                                            @foreach($empleados as $empleado)
                                                <option value="{{ $empleado->id }}"
                                                        {{ old('empleado_id', $usuario->empleado_id ?? '') == $empleado->id ? 'selected' : '' }}>
                                                    {{ $empleado->nombre_completo }} - {{ $empleado->numero_documento }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Vincula el usuario a un empleado de nómina
                                        </p>
                                    </div>

                                    <div class="flex items-center">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <div class="relative">
                                                <input type="checkbox" name="activo" value="1"
                                                       {{ old('activo', $usuario->activo ?? true) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Usuario Activo</span>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Puede iniciar sesión en el sistema</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Descripción de roles --}}
                            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Descripción de Roles:</h4>
                                <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                                    <li><strong>Administrador:</strong> Acceso total al sistema</li>
                                    <li><strong>Recursos Humanos:</strong> Gestión de empleados, nóminas y novedades</li>
                                    <li><strong>Contador:</strong> Acceso a reportes y asientos contables</li>
                                    <li><strong>Supervisor:</strong> Aprobación de nóminas y reportes</li>
                                    <li><strong>Solo Consulta:</strong> Solo puede ver información, sin modificar</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-between pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('admin.usuarios.index') }}"
                               class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                                {{ isset($usuario) ? 'Actualizar Usuario' : 'Crear Usuario' }}
                            </button>
                        </div>
                    </form>

                    {{-- Resetear contraseña (solo en edición) --}}
                    @if(isset($usuario) && $usuario->id !== auth()->id())
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Acciones Rápidas</h4>
                            <div class="flex gap-3 flex-wrap">
                                <form method="POST" action="{{ route('admin.usuarios.toggle', $usuario) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 text-sm {{ $usuario->activo ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200' }} rounded-lg transition">
                                        {{ $usuario->activo ? '⏸ Desactivar Usuario' : '▶ Activar Usuario' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}"
                                      onsubmit="return confirm('¿Eliminar este usuario? Se puede restaurar luego.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-4 py-2 text-sm bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 rounded-lg transition">
                                        🗑 Eliminar Usuario
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>