<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Liquidación de Nómina
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Header --}}
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Liquidación de Nómina</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Proceso guiado de liquidación paso a paso</p>
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

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Wizard Progress --}}
                    <div class="mb-8">
                        <div class="flex items-center justify-between max-w-4xl mx-auto">
                            @foreach(['Datos Básicos', 'Empleados', 'Novedades', 'Preliquidación', 'Confirmación'] as $index => $stepName)
                                <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                                    <div class="flex flex-col items-center">
                                        <div class="rounded-full h-12 w-12 flex items-center justify-center border-2 
                                            {{ $currentStep > $index + 1 ? 'border-green-600 bg-green-600 text-white' : 
                                               ($currentStep === $index + 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-400') }}">
                                            @if($currentStep > $index + 1)
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                                </svg>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <span class="mt-2 text-xs font-medium {{ $currentStep === $index + 1 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $stepName }}
                                        </span>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="flex-1 h-1 {{ $currentStep > $index + 1 ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600' }} mx-2"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Formulario del Wizard --}}
                    <form method="POST" action="{{ $currentStep < 5 ? route('nomina.nominas.wizard.guardar') : route('nomina.nominas.procesar') }}" class="max-w-5xl mx-auto">
                        @csrf
                        <input type="hidden" name="current_step" value="{{ $currentStep }}">

                        {{-- PASO 1: Datos Básicos --}}
                        @if($currentStep === 1)
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8">
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 1: Datos Básicos de la Nómina</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Tipo de Nómina --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Nómina *</label>
                                        <select name="tipo_nomina_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccione...</option>
                                            @foreach($tiposNomina ?? [] as $tipo)
                                                <option value="{{ $tipo->id }}" {{ old('tipo_nomina_id', $datosNomina['tipo_nomina_id'] ?? '') == $tipo->id ? 'selected' : '' }}>
                                                    {{ $tipo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Período --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Período *</label>
                                        <select name="periodo_nomina_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccione...</option>
                                            @foreach($periodosNomina ?? [] as $periodo)
                                                <option value="{{ $periodo->id }}" {{ old('periodo_nomina_id', $datosNomina['periodo_nomina_id'] ?? '') == $periodo->id ? 'selected' : '' }}>
                                                    {{ $periodo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Nombre --}}
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de la Nómina *</label>
                                        <input type="text" name="nombre" value="{{ old('nombre', $datosNomina['nombre'] ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    {{-- Fechas --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Inicio *</label>
                                        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $datosNomina['fecha_inicio'] ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Fin *</label>
                                        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', $datosNomina['fecha_fin'] ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Pago *</label>
                                        <input type="date" name="fecha_pago" value="{{ old('fecha_pago', $datosNomina['fecha_pago'] ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    {{-- Opciones --}}
                                    <div class="md:col-span-2 space-y-3 border-t dark:border-gray-600 pt-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="incluir_seguridad_social" value="1" {{ old('incluir_seguridad_social', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Incluir Seguridad Social</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="incluir_parafiscales" value="1" {{ old('incluir_parafiscales', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Incluir Parafiscales</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="incluir_provisiones" value="1" {{ old('incluir_provisiones', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 mr-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Incluir Provisiones</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                        Continuar →
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- PASO 2: Empleados --}}
                        @if($currentStep === 2)
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8">
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 2: Selección de Empleados</h3>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="select-all" class="rounded mr-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Seleccionar todos</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 gap-3 max-h-96 overflow-y-auto">
                                    @foreach($empleados ?? [] as $empleado)
                                        <label class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                                            <input type="checkbox" name="empleados[]" value="{{ $empleado->id }}" 
                                                   {{ in_array($empleado->id, old('empleados', $datosNomina['empleados'] ?? [])) ? 'checked' : '' }}
                                                   class="rounded mr-3 empleado-checkbox">
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $empleado->nombre_completo }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $empleado->numero_documento }} - {{ $empleado->cargo }}</p>
                                            </div>
                                            <p class="font-mono text-green-600 dark:text-green-400">${{ number_format($empleado->salario_basico, 0) }}</p>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="flex justify-between mt-6">
                                    <a href="{{ route('nomina.nominas.liquidar', ['step' => 1]) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        ← Anterior
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                        Continuar →
                                    </button>
                                </div>

                                <script>
                                (function() {
                                    const selectAll = document.getElementById('select-all');
                                    const employeeCheckboxes = document.querySelectorAll('.empleado-checkbox');
                                    
                                    if (!selectAll || employeeCheckboxes.length === 0) {
                                        return;
                                    }
                                    
                                    function updateSelectAllState() {
                                        const totalCheckboxes = employeeCheckboxes.length;
                                        const checkedCheckboxes = document.querySelectorAll('.empleado-checkbox:checked').length;
                                        
                                        selectAll.checked = checkedCheckboxes === totalCheckboxes;
                                        selectAll.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
                                    }
                                    
                                    selectAll.addEventListener('change', function() {
                                        employeeCheckboxes.forEach(checkbox => {
                                            checkbox.checked = selectAll.checked;
                                        });
                                    });
                                    
                                    employeeCheckboxes.forEach(checkbox => {
                                        checkbox.addEventListener('change', updateSelectAllState);
                                    });
                                    
                                    updateSelectAllState();
                                })();
                                </script>
                            </div>
                        @endif

                        {{-- PASO 3: Novedades --}}
                        @if($currentStep === 3)
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8">
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 3: Novedades (Opcional)</h3>
                                
                                <p class="text-gray-600 dark:text-gray-400 mb-4">Las novedades ya registradas se aplicarán automáticamente. Puede omitir este paso.</p>

                                <div class="flex justify-between mt-6">
                                    <a href="{{ route('nomina.nominas.liquidar', ['step' => 2]) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        ← Anterior
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                        Continuar →
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- PASO 4: Preliquidación --}}
                        @if($currentStep === 4)
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8">
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 4: Preliquidación</h3>
                                
                                @if($preliquidacion)
                                    {{-- Resumen General --}}
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                        <div class="bg-blue-50 dark:bg-blue-800 p-4 rounded-lg">
                                            <p class="text-sm text-gray-600 dark:text-gray-700">Total Devengado</p>
                                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                                ${{ number_format($preliquidacion['total_devengado'], 0) }}
                                            </p>
                                        </div>
                                        <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                                            <p class="text-sm text-gray-600 dark:text-gray-800">Total Deducciones</p>
                                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                                ${{ number_format($preliquidacion['total_deducciones'], 0) }}
                                            </p>
                                        </div>
                                        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                                            <p class="text-sm text-gray-600 dark:text-gray-800">Total Neto a Pagar</p>
                                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                                ${{ number_format($preliquidacion['total_neto'], 0) }}
                                            </p>
                                        </div>
                                        <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                                            <p class="text-sm text-gray-600 dark:text-gray-800">Costo Total Empleador</p>
                                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                                ${{ number_format($preliquidacion['costo_total_empleador'], 0) }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Detalles de Seguridad Social y Otros --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Seguridad Social Empleado</h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Salud (4%)</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_salud_empleado'], 0) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Pensión (4%)</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_pension_empleado'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Seguridad Social Empleador</h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Salud (8.5%)</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_salud_empleador'], 0) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Pensión (12%)</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_pension_empleador'], 0) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">ARL (0.522%)</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_arl_empleador'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Parafiscales y Provisiones</h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Parafiscales (9%)</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_parafiscales'], 0) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Provisiones</span>
                                                    <span class="font-mono text-gray-800 dark:text-gray-200">${{ number_format($preliquidacion['total_provisiones'], 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tabla de empleados --}}
                                    <div class="bg-white dark:bg-gray-600 rounded-lg overflow-hidden mb-6">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-500">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Empleado</th>
                                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Salario</th>
                                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Devengado</th>
                                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Deducciones</th>
                                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Neto</th>
                                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Costo Empleador</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-500">
                                                    @foreach($preliquidacion['empleados'] as $detalle)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">
                                                            {{ $detalle['empleado']->nombre_completo }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-200">
                                                            ${{ number_format($detalle['salario_basico'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-blue-600 dark:text-blue-400">
                                                            ${{ number_format($detalle['devengado'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-red-600 dark:text-red-400">
                                                            ${{ number_format($detalle['deducciones'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-green-600 dark:text-green-400">
                                                            ${{ number_format($detalle['neto'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-purple-600 dark:text-purple-400">
                                                            ${{ number_format($detalle['costo_empleador'], 0) }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-gray-50 dark:bg-gray-800">
                                                    <tr class="font-bold">
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">
                                                            TOTALES ({{ $preliquidacion['numero_empleados'] }} empleados)
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-200">
                                                            ${{ number_format(collect($preliquidacion['empleados'])->sum('salario_basico'), 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-blue-600 dark:text-blue-400">
                                                            ${{ number_format($preliquidacion['total_devengado'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-red-600 dark:text-red-400">
                                                            ${{ number_format($preliquidacion['total_deducciones'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-green-600 dark:text-green-400">
                                                            ${{ number_format($preliquidacion['total_neto'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-right font-mono text-purple-600 dark:text-purple-400">
                                                            ${{ number_format($preliquidacion['costo_total_empleador'], 0) }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                                        <p class="text-yellow-800 dark:text-yellow-200">No hay empleados seleccionados para calcular la preliquidación.</p>
                                    </div>
                                @endif

                                <div class="flex justify-between mt-6">
                                    <a href="{{ route('nomina.nominas.liquidar', ['step' => 3]) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        ← Anterior
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                        Continuar →
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- PASO 5: Confirmación --}}
                        @if($currentStep === 5)
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8">
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 5: Confirmación</h3>
                                
                                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6 mb-6">
                                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-4">Resumen de la Liquidación</h4>
                                    <div class="space-y-2 text-sm">
                                        <p><strong>Nombre:</strong> {{ $datosNomina['nombre'] ?? 'N/A' }}</p>
                                        <p><strong>Período:</strong> {{ $datosNomina['fecha_inicio'] ?? 'N/A' }} - {{ $datosNomina['fecha_fin'] ?? 'N/A' }}</p>
                                        <p><strong>Empleados seleccionados:</strong> {{ count($datosNomina['empleados'] ?? []) }}</p>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-6">
                                    <a href="{{ route('nomina.nominas.liquidar', ['step' => 4]) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        ← Anterior
                                    </a>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                                        ✓ Confirmar y Guardar Nómina
                                    </button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>