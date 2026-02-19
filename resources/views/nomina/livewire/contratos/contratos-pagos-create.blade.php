<x-app-layout>
    <div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Registrar Pago de Contrato</h1>
                <p class="text-gray-600 mt-1 dark:text-gray-400">{{ $contrato->numero_contrato ?? 'Nuevo pago' }}</p>
            </div>
            <a href="{{ route('nomina.contratos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>

        {{-- Errores --}}
        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Información del Contrato --}}
        @if(isset($contrato))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-blue-900 mb-3">Información del Contrato</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Contratista</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $contrato->contratista ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Valor Total</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-200">${{ number_format($contrato->valor_total ?? 0, 0) }}</p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Saldo Pendiente</p>
                    <p class="font-semibold text-green-600 dark:text-green-400">
                        ${{ number_format(($contrato->valor_total ?? 0) - ($contrato->valor_ejecutado ?? 0), 0) }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Formulario --}}
        <form action="{{ route('nomina.contratos.pagos.store', $contrato ?? 0) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Datos del Pago --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Datos del Pago</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 mb-2">
                            Número de Pago <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="numero_pago" value="{{ old('numero_pago') }}" required
                            placeholder="Ej: PAGO-001"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha de Pago <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Valor Bruto <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="valor_bruto" value="{{ old('valor_bruto') }}" required step="0.01"
                            placeholder="0.00"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            id="valor_bruto" onchange="calcularNeto()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Período</label>
                        <input type="text" name="periodo" value="{{ old('periodo') }}"
                            placeholder="Ej: Enero 2026"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- Deducciones --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Deducciones</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Retención en la Fuente</label>
                        <input type="number" name="retencion_fuente" value="{{ old('retencion_fuente', 0) }}" step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            id="retencion_fuente" onchange="calcularNeto()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ReteICA</label>
                        <input type="number" name="reteica" value="{{ old('reteica', 0) }}" step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            id="reteica" onchange="calcularNeto()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ReteIVA</label>
                        <input type="number" name="reteiva" value="{{ old('reteiva', 0) }}" step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            id="reteiva" onchange="calcularNeto()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Otras Deducciones</label>
                        <input type="number" name="otras_deducciones" value="{{ old('otras_deducciones', 0) }}" step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            id="otras_deducciones" onchange="calcularNeto()">
                    </div>

                    <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">Valor Neto a Pagar:</span>
                            <span class="text-2xl font-bold text-green-600" id="valor_neto">$0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Información Adicional</h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Concepto del Pago</label>
                        <input type="text" name="concepto" value="{{ old('concepto') }}"
                            placeholder="Ej: Pago parcial según acta de entrega"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observaciones</label>
                        <textarea name="observaciones" rows="3" 
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('nomina.contratos.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Registrar Pago
                </button>
            </div>
        </form>
    </div>
    </div>

    @push('scripts')
    <script>
    function calcularNeto() {
        const valorBruto = parseFloat(document.getElementById('valor_bruto').value) || 0;
        const retencionFuente = parseFloat(document.getElementById('retencion_fuente').value) || 0;
        const reteica = parseFloat(document.getElementById('reteica').value) || 0;
        const reteiva = parseFloat(document.getElementById('reteiva').value) || 0;
        const otrasDeducciones = parseFloat(document.getElementById('otras_deducciones').value) || 0;
        
        const valorNeto = valorBruto - retencionFuente - reteica - reteiva - otrasDeducciones;
        
        document.getElementById('valor_neto').textContent = '$' + valorNeto.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    // Calcular al cargar
    document.addEventListener('DOMContentLoaded', calcularNeto);
    </script>
    @endpush
</x-app-layout>