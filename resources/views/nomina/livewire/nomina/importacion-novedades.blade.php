<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Importar Novedades</h1>
                        <p class="text-gray-600 mt-1 dark:text-gray-400">Carga masiva de novedades desde archivo Excel</p>
                    </div>
                    <a href="{{ route('nomina.novedades.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>

            @php
                $step = $step ?? 1;
            @endphp

            {{-- Wizard de pasos --}}
            <div class="mb-8">
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    @foreach(['Cargar Archivo', 'Validar Datos', 'Confirmar'] as $index => $stepName)
                        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                            <div class="flex flex-col items-center">
                                <div class="rounded-full h-12 w-12 flex items-center justify-center border-2 
                                    {{ $step > $index + 1 ? 'border-green-600 bg-green-600 text-white' : 
                                       ($step === $index + 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-400') }}">
                                    @if($step > $index + 1)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                        </svg>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-medium {{ $step === $index + 1 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $stepName }}
                                </span>
                            </div>
                            @if(!$loop->last)
                                <div class="flex-1 h-1 {{ $step > $index + 1 ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600' }} mx-2"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Mensajes --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            {{-- PASO 1: Cargar Archivo --}}
            @if($step == 1)
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8 max-w-4xl mx-auto">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 1: Cargar Archivo de Novedades</h3>
                
                {{-- Instrucciones --}}
                <div class="mb-6 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-400 p-4 rounded">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-3">Formato requerido del archivo:</h4>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 list-disc list-inside space-y-1">
                        <li>Formato: Excel (.xlsx, .xls) o CSV (.csv)</li>
                        <li><strong>Columnas requeridas:</strong></li>
                        <li class="ml-6">• Documento (número de documento del empleado)</li>
                        <li class="ml-6">• Concepto (código: HED, BON, INC, etc.)</li>
                        <li class="ml-6">• Fecha (formato: YYYY-MM-DD o DD/MM/YYYY)</li>
                        <li class="ml-6">• Cantidad (número de horas, días, etc.)</li>
                        <li class="ml-6">• Valor Unitario (valor por unidad)</li>
                        <li><strong>Columnas opcionales:</strong> Observaciones, Período</li>
                    </ul>
                </div>

                {{-- Ejemplo de estructura --}}
                <div class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Ejemplo de estructura:</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border border-gray-300 dark:border-gray-600">
                            <thead class="bg-gray-200 dark:bg-gray-700">
                                <tr>
                                    <th class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-left">Documento</th>
                                    <th class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-left">Concepto</th>
                                    <th class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-left">Fecha</th>
                                    <th class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-right">Cantidad</th>
                                    <th class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-right">Valor Unit.</th>
                                    <th class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-left">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800">
                                <tr>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">1234567890</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">HED</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">2026-02-10</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-right">8</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-right">15000</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">Horas extras diurnas</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">9876543210</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">INC</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">2026-02-10</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-right">3</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2 text-right">0</td>
                                    <td class="border border-gray-300 dark:border-gray-600 text-black dark:text-gray-200 px-2 py-2">Incapacidad médica</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Formulario de carga --}}
                <form action="{{ route('nomina.novedades.procesar-importacion') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Seleccionar Archivo <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-center w-full">
                            <label class="w-full flex flex-col items-center justify-center px-4 py-6 bg-gray-50 dark:bg-gray-800 text-blue-500 rounded-lg border-2 border-dashed border-blue-300 dark:border-blue-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-8 h-8 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M16.88 9.1A4 4 0 0116 7a6 6 0 1-12 0 4 4 0 01.9-2.9 2 2 0 00.4 2.9"/>
                                    <path d="M10 14l.5-1.5m0 0L9.5 11m.5 2.5L11 11m0 0l1.5.5m-1.5-.5L9 12m0 3v2m0-2v-1m0 1H8m4 0h4m-4 0v2m0-2v-1"/>
                                </svg>
                                <span class="text-sm font-medium">Hacer clic aquí o arrastrar archivo</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Excel (.xlsx, .xls) o CSV (.csv) - Máx. 5MB</span>
                                <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                            Continuar →
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- PASO 2: Validar Datos --}}
            @if($step == 2)
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8 max-w-6xl mx-auto">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Paso 2: Validar Datos</h3>
                
                @if(isset($novedades) && count($novedades) > 0)
                    <div class="mb-4 flex justify-between items-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Se encontraron <strong>{{ count($novedades) }}</strong> novedades válidas
                            @if(isset($errores) && count($errores) > 0)
                                y <strong class="text-red-600">{{ count($errores) }}</strong> con errores
                            @endif
                        </p>
                    </div>

                    {{-- Mostrar errores primero si existen --}}
                    @if(isset($errores) && count($errores) > 0)
                        <div class="mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                            <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Errores encontrados:</h4>
                            <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                                @foreach($errores as $error)
                                    <li>Fila {{ $error['fila'] }}: {{ implode(', ', $error['errores']) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Empleado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Concepto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cantidad</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Valor Unit.</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($novedades as $novedad)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                        {{ $novedad['empleado'] ?? 'N/A' }}<br>
                                        <span class="text-xs text-gray-500">{{ $novedad['documento'] ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                        {{ $novedad['concepto'] ?? 'N/A' }}<br>
                                        <span class="text-xs text-gray-500">{{ $novedad['codigo_concepto'] ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                        {{ isset($novedad['fecha']) ? \Carbon\Carbon::parse($novedad['fecha'])->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-200">
                                        {{ number_format($novedad['cantidad'] ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-mono text-gray-900 dark:text-gray-200">
                                        ${{ number_format($novedad['valor_unitario'] ?? 0, 0) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-mono font-semibold text-gray-900 dark:text-gray-200">
                                        ${{ number_format($novedad['valor_total'] ?? 0, 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">
                                        Total:
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono font-bold text-gray-900 dark:text-gray-200">
                                        ${{ number_format(collect($novedades)->sum('valor_total'), 0) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <form action="{{ route('nomina.novedades.confirmar-importacion') }}" method="POST">
                        @csrf
                        <div class="mt-6 flex justify-between">
                            <a href="{{ route('nomina.novedades.importar') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                ← Anterior
                            </a>
                            @if(count($novedades) > 0)
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                                    Confirmar Importación ({{ count($novedades) }} novedades) →
                                </button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                        <p class="text-yellow-800 dark:text-yellow-200">No se encontraron datos válidos en el archivo</p>
                    </div>
                    <div class="mt-6 flex justify-start">
                        <a href="{{ route('nomina.novedades.importar') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            ← Anterior
                        </a>
                    </div>
                @endif
            </div>
            @endif

            {{-- PASO 3: Confirmación --}}
            @if($step == 3)
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-8 max-w-2xl mx-auto text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">¡Importación Exitosa!</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Las novedades se han importado correctamente</p>
                
                <div class="flex justify-center gap-4">
                    <a href="{{ route('nomina.novedades.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                        Ver Novedades
                    </a>
                    <a href="{{ route('nomina.novedades.importar') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        Nueva Importación
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>