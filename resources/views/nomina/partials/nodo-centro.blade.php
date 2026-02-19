{{-- resources/views/nomina/partials/nodo-centro.blade.php --}}
<div class="border-l-2 border-gray-300 dark:border-gray-600 {{ $nivel > 0 ? 'ml-6' : '' }}">
    <div class="flex items-center py-2 px-4 hover:bg-gray-50 dark:hover:bg-gray-600 rounded group">
        {{-- Indicador de nivel --}}
        <div class="flex items-center mr-3">
            @if($nodo->hijos->count() > 0)
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            @else
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif
        </div>

        {{-- Badge de nivel --}}
        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 mr-3">
            N{{ $nivel + 1 }}
        </span>

        {{-- Código --}}
        <span class="font-mono font-semibold text-gray-900 dark:text-gray-200 mr-3">
            {{ $nodo->codigo }}
        </span>

        {{-- Nombre --}}
        <span class="text-gray-900 dark:text-gray-200 flex-1">
            {{ $nodo->nombre }}
        </span>

        {{-- Descripción --}}
        @if($nodo->descripcion)
            <span class="text-sm text-gray-500 dark:text-gray-400 mr-3">
                {{ Str::limit($nodo->descripcion, 50) }}
            </span>
        @endif

        {{-- Estado --}}
        @if($nodo->activo)
            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 mr-3">
                Activo
            </span>
        @else
            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 mr-3">
                Inactivo
            </span>
        @endif

        {{-- Acciones --}}
        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <a href="{{ route('nomina.centros-costo.edit', $nodo) }}" 
               class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <form action="{{ route('nomina.centros-costo.destroy', $nodo) }}" method="POST" class="inline" 
                  onsubmit="return confirm('¿Está seguro de eliminar este centro de costo?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Hijos recursivos --}}
    @if($nodo->hijos->count() > 0)
        <div class="ml-4">
            @foreach($nodo->hijos as $hijo)
                @include('nomina.partials.nodo-centro', ['nodo' => $hijo, 'nivel' => $nivel + 1])
            @endforeach
        </div>
    @endif
</div>