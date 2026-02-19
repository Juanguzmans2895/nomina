<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Registro de Pagos - {{ $contrato->numero_contrato }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    {{-- Información del Contrato --}}
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Registro de Pagos</h2>
                        <div class="p-4 bg-blue-50 dark:bg-gray-700 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Contrato</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $contrato->numero_contrato }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Contratista</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $contrato->nombre_contratista }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Valor Total</p>
                                    <p class="font-semibold text-green-600 dark:text-green-400">${{ number_format($contrato->valor_total, 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Saldo Pendiente</p>
                                    <p class="font-semibold text-red-600 dark:text-red-400">${{ number_format($contrato->saldo_pendiente, 0) }}</p>
                                </div>
                            </div>
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

                    {{-- Botones de acción --}}
                    <div class="flex gap-3 mb-4">
                        <a href="{{ route('nomina.contratos.pagos.create', $contrato) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Registrar Pago
                        </a>
                        <a href="{{ route('nomina.contratos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                            Volver a Contratos
                        </a>
                    </div>

                    {{-- Tabla de Pagos --}}
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Número</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Bruto</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Retención</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Neto</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse($contrato->pagos ?? [] as $pago)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-900 dark:text-gray-200">
                                            {{ $pago->numero_pago }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                            {{ $pago->fecha_pago->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-gray-900 dark:text-gray-200">
                                            ${{ number_format($pago->valor_bruto, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-red-600 dark:text-red-400">
                                            ${{ number_format($pago->retencion_fuente ?? 0, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-green-600 dark:text-green-400">
                                            ${{ number_format($pago->valor_neto, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $estado = $pago->pagado ? 'pagado' : ($pago->aprobado ? 'aprobado' : 'pendiente');
                                                $estadoClasses = [
                                                    'pagado' => 'bg-green-100 text-green-800',
                                                    'aprobado' => 'bg-blue-100 text-blue-800',
                                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full {{ $estadoClasses[$estado] }}">
                                                {{ ucfirst($estado) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            @if(!$pago->aprobado)
                                                <form action="{{ route('nomina.contratos.pagos.aprobar', [$contrato, $pago]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400">
                                                        Aprobar
                                                    </button>
                                                </form>
                                            @endif
                                            @if($pago->aprobado && !$pago->pagado)
                                                <form action="{{ route('nomina.contratos.pagos.marcar-pagado', [$contrato, $pago]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                        Marcar Pagado
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No hay pagos registrados para este contrato
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Información adicional --}}
                    @if($contrato->pagos->count() > 0)
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Pagado</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    ${{ number_format($contrato->pagos->sum('valor_neto'), 0) }}
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Retenciones</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    ${{ number_format($contrato->pagos->sum('retencion_fuente'), 0) }}
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pagos Realizados</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $contrato->pagos->where('pagado', true)->count() }} / {{ $contrato->pagos->count() }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>