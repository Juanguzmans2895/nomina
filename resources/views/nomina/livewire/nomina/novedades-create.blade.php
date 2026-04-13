@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                
                <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200">Nueva Novedad</h2>

                <form method="POST" action="{{ route('nomina.novedades.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        
                        {{-- Empleado --}}
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2">Empleado *</label>
                            <select name="empleado_id" id="empleado_id" required class="w-full rounded border-gray-300 dark:bg-gray-700">
                                <option value="">Seleccione...</option>
                                @foreach($empleados as $e)
                                    <option value="{{ $e->id }}" data-salario="{{ $e->salario_basico }}">{{ $e->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Concepto --}}
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2">Concepto *</label>
                            <select name="concepto_id" id="concepto_id" required class="w-full rounded border-gray-300 dark:bg-gray-700">
                                <option value="">Seleccione...</option>
                                @foreach($conceptos as $c)
                                    <option value="{{ $c->id }}" data-formula="{{ $c->formula }}" data-recargo="{{ $c->porcentaje_recargo }}">
                                        {{ $c->codigo }} - {{ $c->nombre }} @if($c->porcentaje_recargo)({{ $c->porcentaje_recargo }}%)@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Período --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Período *</label>
                            <select name="periodo_id" required class="w-full rounded border-gray-300 dark:bg-gray-700">
                                <option value="">Seleccione...</option>
                                @foreach($periodos as $p)
                                    <option value="{{ $p->id }}" {{ $p->codigo == now()->format('Ym') ? 'selected' : '' }}>{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Fecha *</label>
                            <input type="date" name="fecha" value="{{ now()->toDateString() }}" required class="w-full rounded border-gray-300 dark:bg-gray-700">
                        </div>

                        {{-- Cantidad --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Cantidad *</label>
                            <input type="number" name="cantidad" id="cantidad" min="0" step="1" value="0" required class="w-full rounded border-gray-300 dark:bg-gray-700">
                        </div>

                        {{-- Valor Unitario --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Valor Unitario</label>
                            <input type="number" id="valor_unitario" name="valor_unitario" step="0.01" readonly class="w-full rounded bg-gray-100 dark:bg-gray-600">
                        </div>

                    </div>

                    {{-- Total --}}
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded">
                        <div class="flex justify-between">
                            <span class="font-medium">Valor Total:</span>
                            <span class="text-2xl font-bold text-blue-600" id="valor_total_display">$0</span>
                        </div>
                        <input type="hidden" name="valor_total" id="valor_total">
                    </div>

                    {{-- Observaciones --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-2">Observaciones</label>
                        <textarea name="observaciones" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-700"></textarea>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('nomina.novedades.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const empSelect = document.getElementById('empleado_id');
        const concSelect = document.getElementById('concepto_id');
        const cantInput = document.getElementById('cantidad');
        const valUnitInput = document.getElementById('valor_unitario');
        const valTotalInput = document.getElementById('valor_total');
        const valTotalDisplay = document.getElementById('valor_total_display');

        function calcular() {
            const empOpt = empSelect.options[empSelect.selectedIndex];
            const concOpt = concSelect.options[concSelect.selectedIndex];
            const cant = parseFloat(cantInput.value) || 0;

            if (!empOpt || !concOpt || cant === 0) {
                valUnitInput.value = '0';
                valTotalInput.value = '0';
                valTotalDisplay.textContent = '$0';
                return;
            }

            const salario = parseFloat(empOpt.dataset.salario) || 0;
            const formula = concOpt.dataset.formula;
            const recargo = parseFloat(concOpt.dataset.recargo) || 0;

            let valUnit = 0, valTotal = 0;

            if (formula) {
                const f = formula.replace(/salario_basico|salario/g, salario).replace(/cantidad/g, cant).replace(/ibc/g, salario);
                try { valTotal = eval(f); valUnit = valTotal / cant; } catch(e) { console.error(e); }
            } else if (recargo) {
                valUnit = (salario / 240) * (1 + recargo/100);
                valTotal = valUnit * cant;
            }

            valUnitInput.value = valUnit.toFixed(2);
            valTotalInput.value = Math.round(valTotal);
            valTotalDisplay.textContent = '$' + Math.round(valTotal).toLocaleString('es-CO');
        }

        empSelect.addEventListener('change', calcular);
        concSelect.addEventListener('change', calcular);
        cantInput.addEventListener('input', calcular);
    </script>
@endpush
@endsection