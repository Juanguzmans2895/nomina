<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Generador de Reportes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-6">Generador de Reportes</h1>

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

                    @if (session('info'))
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                            {{ session('info') }}
                        </div>
                    @endif

                    {{-- Selector de Tipo de Reporte --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Seleccione el Tipo de Reporte</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {{-- Desprendible de Pago --}}
                            <a href="{{ route('nomina.reportes.index', ['tipo' => 'desprendible']) }}" 
                               class="p-6 border-2 rounded-lg transition-all {{ request('tipo') === 'desprendible' ? 'border-blue-600 bg-blue-50 dark:bg-blue-700' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 {{ request('tipo') === 'desprendible' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h4 class="font-semibold mb-1 text-gray-900 dark:text-gray-200">Desprendible de Pago</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Generar desprendibles individuales o masivos</p>
                                </div>
                            </a>

                            {{-- Certificados Laborales --}}
                            <a href="{{ route('nomina.reportes.index', ['tipo' => 'certificado']) }}" 
                               class="p-6 border-2 rounded-lg transition-all {{ request('tipo') === 'certificado' ? 'border-blue-600 bg-blue-50 dark:bg-blue-700' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 {{ request('tipo') === 'certificado' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <h4 class="font-semibold mb-1 text-gray-900 dark:text-gray-200">Certificados Laborales</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Certificados laborales y de ingresos</p>
                                </div>
                            </a>

                            {{-- Reportes Consolidados --}}
                            <a href="{{ route('nomina.reportes.index', ['tipo' => 'consolidado']) }}" 
                               class="p-6 border-2 rounded-lg transition-all {{ request('tipo') === 'consolidado' ? 'border-blue-600 bg-blue-50 dark:bg-blue-700' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 {{ request('tipo') === 'consolidado' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <h4 class="font-semibold mb-1 text-gray-900 dark:text-gray-200">Reportes Consolidados</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Consolidados de nómina y seguridad social</p>
                                </div>
                            </a>

                            {{-- Archivo PILA --}}
                            <a href="{{ route('nomina.reportes.index', ['tipo' => 'pila']) }}" 
                               class="p-6 border-2 rounded-lg transition-all {{ request('tipo') === 'pila' ? 'border-blue-600 bg-blue-50 dark:bg-blue-700' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 {{ request('tipo') === 'pila' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <h4 class="font-semibold mb-1 text-gray-900 dark:text-gray-200">Archivo PILA</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Generar archivo para pago de seguridad social</p>
                                </div>
                            </a>

                            {{-- Reportes Excel --}}
                            <a href="{{ route('nomina.reportes.index', ['tipo' => 'excel']) }}" 
                               class="p-6 border-2 rounded-lg transition-all {{ request('tipo') === 'excel' ? 'border-blue-600 bg-blue-50 dark:bg-blue-700' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 {{ request('tipo') === 'excel' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h4 class="font-semibold mb-1 text-gray-900 dark:text-gray-200">Reportes Excel</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Exportar datos a Excel</p>
                                </div>
                            </a>

                            {{-- Reporte de Provisiones --}}
                            <a href="{{ route('nomina.reportes.index', ['tipo' => 'provisiones']) }}" 
                               class="p-6 border-2 rounded-lg transition-all {{ request('tipo') === 'provisiones' ? 'border-blue-600 bg-blue-50 dark:bg-blue-700' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-500' }}">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-3 {{ request('tipo') === 'provisiones' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <h4 class="font-semibold mb-1 text-gray-900 dark:text-gray-200">Reporte de Provisiones</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Saldos de cesantías, prima y vacaciones</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- Formularios según tipo seleccionado --}}
                    @if(request('tipo'))
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                Configuración del Reporte
                            </h3>

                            {{-- DESPRENDIBLES --}}
                            @if(request('tipo') === 'desprendible')
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Seleccione una nómina de la lista para ver y descargar desprendibles:
                                    </p>
                                    
                                    <div class="space-y-2">
                                        @forelse($nominas ?? [] as $nomina)
                                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-gray-200">
                                                        {{ $nomina->numero_nomina }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $nomina->periodo->nombre ?? 'N/A' }} - {{ $nomina->numero_empleados ?? 0 }} empleados
                                                    </p>
                                                </div>
                                                <a href="{{ route('nomina.reportes.desprendibles-masivo', $nomina->id) }}" 
                                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                    Generar Desprendibles
                                                </a>
                                            </div>
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                                No hay nóminas disponibles
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- CERTIFICADOS --}}
                            @if(request('tipo') === 'certificado')
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Seleccione un empleado y el tipo de certificado:
                                    </p>
                                    
                                    <div class="space-y-2">
                                        @forelse($empleados ?? [] as $empleado)
                                            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div>
                                                        <p class="font-semibold text-gray-900 dark:text-gray-200">
                                                            {{ $empleado->nombre_completo }}
                                                        </p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $empleado->numero_documento }} - {{ $empleado->cargo ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex gap-2">
                                                    <a href="{{ route('nomina.reportes.certificado-laboral', $empleado->id) }}" 
                                                       class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                                        Certificado Laboral
                                                    </a>
                                                    <a href="{{ route('nomina.reportes.certificado-ingresos', [$empleado->id, date('Y')]) }}" 
                                                       class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                                        Cert. Ingresos {{ date('Y') }}
                                                    </a>
                                                    <a href="{{ route('nomina.reportes.certificado-cesantias', $empleado->id) }}" 
                                                       class="px-3 py-1.5 bg-gray-600 text-white text-sm rounded hover:bg-gray-800">
                                                        Cert. Cesantías
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                                No hay empleados disponibles
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- CONSOLIDADOS --}}
                            @if(request('tipo') === 'consolidado')
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Seleccione una nómina para generar el reporte consolidado:
                                    </p>
                                    
                                    <div class="space-y-2">
                                        @forelse($nominas ?? [] as $nomina)
                                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-gray-200">
                                                        {{ $nomina->numero_nomina }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $nomina->periodo->nombre ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="flex gap-2">
                                                    <a href="{{ route('nomina.reportes.consolidado', $nomina->id) }}" 
                                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                        Consolidado General
                                                    </a>
                                                    <a href="{{ route('nomina.reportes.consolidado-seguridad-social', $nomina->id) }}" 
                                                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                                        Seguridad Social
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                                No hay nóminas disponibles
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- PILA --}}
                            @if(request('tipo') === 'pila')
                                <div class="space-y-4">
                                    <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-4">
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            El archivo PILA es el formato requerido por el operador de información para el pago de aportes a seguridad social.
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        @forelse($nominas ?? [] as $nomina)
                                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-gray-200">
                                                        {{ $nomina->numero_nomina }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $nomina->periodo->nombre ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <a href="{{ route('nomina.reportes.pila', $nomina->id) }}" 
                                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                    Descargar PILA
                                                </a>
                                            </div>
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                                No hay nóminas disponibles
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- EXCEL --}}
                            @if(request('tipo') === 'excel')
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Exportar datos de nóminas a Excel:
                                    </p>
                                    
                                    <div class="space-y-2">
                                        @forelse($nominas ?? [] as $nomina)
                                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-gray-200">
                                                        {{ $nomina->numero_nomina }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $nomina->periodo->nombre ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <a href="{{ route('nomina.reportes.excel-nomina', $nomina->id) }}" 
                                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                                    Exportar a Excel
                                                </a>
                                            </div>
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                                No hay nóminas disponibles
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            {{-- PROVISIONES --}}
                            @if(request('tipo') === 'provisiones')
                                <div class="space-y-4">
                                    <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            Este reporte generará un archivo Excel con los saldos actuales de cesantías, intereses, prima de servicios y vacaciones de todos los empleados activos.
                                        </p>
                                    </div>

                                    <div class="flex justify-end">
                                        <a href="{{ route('nomina.reportes.excel-provisiones') }}" 
                                           class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                            Exportar Provisiones
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-8 text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">
                                Seleccione un tipo de reporte para comenzar
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>