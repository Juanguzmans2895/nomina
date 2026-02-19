<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 dark:border-b dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <!-- Dashboard Principal -->
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Dropdown: Nómina -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                                    💰 Nómina
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">
                                    Dashboard & Liquidación
                                </div>
                                <x-dropdown-link href="{{ route('nomina.dashboard-nomina') }}">
                                    📊 Dashboard de Nómina
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('nomina.nominas.liquidar') }}">
                                    🧮 Liquidar Nómina
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('nomina.nominas.historial') }}">
                                    📋 Historial de Nóminas
                                </x-dropdown-link>

                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

                                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">
                                    Novedades
                                </div>
                                <x-dropdown-link href="{{ route('nomina.novedades.index') }}">
                                    ✏️ Gestión de Novedades
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('nomina.novedades.importar') }}">
                                    📥 Importar Novedades
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown: Empleados -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                                    👥 Empleados
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('nomina.empleados.index') }}">
                                    👤 Gestión de Empleados
                                </x-dropdown-link>

                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

                                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">
                                    Configuración
                                </div>
                                <x-dropdown-link href="{{ route('nomina.conceptos.index') }}">
                                    💵 Conceptos de Nómina
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('nomina.centros-costo.index') }}">
                                    🏢 Centros de Costo
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown: Contratos -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                                    📄 Contratos
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('nomina.contratos.index') }}">
                                    📑 Gestión de Contratos
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown: Provisiones & Contabilidad -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                                    💼 Provisiones
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('nomina.provisiones.index') }}">
                                    💰 Consulta de Provisiones
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('nomina.provisiones.asientos') }}">
                                    📒 Asientos Contables
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown: Reportes -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="left" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                                    📊 Reportes
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">
                                    Generación
                                </div>
                                <x-dropdown-link href="{{ route('nomina.reportes.index') }}">
                                    📑 Generador de Reportes
                                </x-dropdown-link>

                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>

                                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">
                                    Tipos de Reporte
                                </div>
                                <div class="px-4 py-1 text-xs text-gray-500 dark:text-gray-400">• Desprendibles de Pago</div>
                                <div class="px-4 py-1 text-xs text-gray-500 dark:text-gray-400">• Certificados Laborales</div>
                                <div class="px-4 py-1 text-xs text-gray-500 dark:text-gray-400">• Consolidados SS</div>
                                <div class="px-4 py-1 text-xs text-gray-500 dark:text-gray-400">• Archivo PILA</div>
                                <div class="px-4 py-1 text-xs text-gray-500 dark:text-gray-400">• Exportaciones Excel</div>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown: Administración (solo admins) ✅ NUEVO -->
                    @if(auth()->user()?->role === 'admin')
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-500 bg-white dark:bg-gray-800 dark:hover:text-red-400 focus:outline-none transition ease-in-out duration-150">
                                    ⚙️ Admin
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">
                                    Administración del Sistema
                                </div>
                                <x-dropdown-link href="{{ route('admin.usuarios.index') }}" :active="request()->routeIs('admin.usuarios.*')">
                                    👤 Gestión de Usuarios
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @livewire('dark-mode-toggle')
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex text-sm border-2 border-transparent rounded-full bg-white dark:bg-gray-800 focus:outline-none focus:border-gray-300 transition">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            </button>
                        @else
                            <span class="inline-flex rounded-md">
                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                    {{ Auth::user()->name }}
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <!-- Rol del usuario -->
                        <div class="block px-4 py-2 text-xs text-gray-400 border-b border-gray-100 dark:border-gray-600">
                            <span class="font-semibold text-gray-600 dark:text-gray-300">{{ Auth::user()->name }}</span><br>
                            <span class="capitalize">{{ Auth::user()->role_nombre ?? Auth::user()->role ?? 'Usuario' }}</span>
                        </div>

                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Account') }}
                        </div>

                        <x-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('API Tokens') }}
                            </x-dropdown-link>
                        @endif

                        {{-- Acceso rápido a usuarios (solo admin) ✅ NUEVO --}}
                        @if(auth()->user()?->role === 'admin')
                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                            <x-dropdown-link href="{{ route('admin.usuarios.index') }}">
                                🔐 Gestión de Usuarios
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- Nómina -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">Nómina</div>
                <x-responsive-nav-link href="{{ route('nomina.dashboard-nomina') }}">
                    📊 Dashboard Nómina
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.nominas.liquidar') }}">
                    🧮 Liquidar Nómina
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.nominas.historial') }}">
                    📋 Historial
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.novedades.index') }}">
                    ✏️ Novedades
                </x-responsive-nav-link>
            </div>

            <!-- Empleados -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-semibold">Empleados</div>
                <x-responsive-nav-link href="{{ route('nomina.empleados.index') }}">
                    👤 Empleados
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.conceptos.index') }}">
                    💵 Conceptos
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.centros-costo.index') }}">
                    🏢 Centros de Costo
                </x-responsive-nav-link>
            </div>

            <!-- Otros -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                <x-responsive-nav-link href="{{ route('nomina.contratos.index') }}">
                    📄 Contratos
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.provisiones.index') }}">
                    💼 Provisiones
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('nomina.reportes.index') }}">
                    📊 Reportes
                </x-responsive-nav-link>
            </div>

            {{-- Administración (solo admins) ✅ NUEVO --}}
            @if(auth()->user()?->role === 'admin')
            <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                <div class="px-4 py-2 text-xs text-red-400 uppercase font-semibold">⚙️ Administración</div>
                <x-responsive-nav-link href="{{ route('admin.usuarios.index') }}" :active="request()->routeIs('admin.usuarios.*')">
                    👤 Gestión de Usuarios
                </x-responsive-nav-link>
            </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    <div class="text-xs text-gray-400 capitalize mt-0.5">
                        {{ Auth::user()->role_nombre ?? Auth::user()->role ?? 'Usuario' }}
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>