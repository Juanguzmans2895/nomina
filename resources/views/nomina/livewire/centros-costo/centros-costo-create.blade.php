@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Crear Centro de Costo</h1>
                <p class="text-gray-600 mt-1">Registrar nuevo centro de costo</p>
            </div>
            <a href="{{ route('nomina.centros-costo.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
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

        {{-- Formulario --}}
        <form action="{{ route('nomina.centros-costo.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}" required
                        placeholder="Ej: CC-001"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @error('codigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                        placeholder="Ej: Recursos Humanos"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('descripcion') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Responsable</label>
                    <input type="text" name="responsable" value="{{ old('responsable') }}"
                        placeholder="Nombre del responsable"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Presupuesto</label>
                    <input type="number" name="presupuesto" value="{{ old('presupuesto') }}" step="0.01"
                        placeholder="0.00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }} 
                            class="rounded border-gray-300 mr-2">
                        <span class="text-sm text-gray-700">Centro de costo activo</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t">
                <a href="{{ route('nomina.centros-costo.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Guardar Centro de Costo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection