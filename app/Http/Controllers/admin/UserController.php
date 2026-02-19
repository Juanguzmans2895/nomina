<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listado de usuarios
     */
    public function index(Request $request)
    {
        $query = User::withTrashed()->latest();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->whereNull('deleted_at')->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $query->whereNull('deleted_at')->where('activo', false);
            } elseif ($request->estado === 'eliminado') {
                $query->whereNotNull('deleted_at');
            }
        } else {
            $query->whereNull('deleted_at');
        }

        $usuarios = $query->paginate(15);

        // Estadísticas
        $stats = [
            'total'      => User::count(),
            'activos'    => User::where('activo', true)->count(),
            'inactivos'  => User::where('activo', false)->count(),
            'admins'     => User::where('role', 'admin')->count(),
        ];

        $roles = User::ROLES;

        return view('admin.usuarios.index', compact('usuarios', 'stats', 'roles'));
    }

    /**
     * Formulario crear usuario
     */
    public function create()
    {
        $roles = User::ROLES;

        $empleados = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->get();

        return view('admin.usuarios.create', compact('roles', 'empleados'));
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => ['required', Rule::in(array_keys(User::ROLES))],
            'activo'      => 'nullable|boolean',
            'empleado_id' => 'nullable|exists:empleados,id',
        ], [
            'name.required'      => 'El nombre es requerido',
            'email.required'     => 'El correo es requerido',
            'email.unique'       => 'Este correo ya está registrado',
            'password.required'  => 'La contraseña es requerida',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'role.required'      => 'El rol es requerido',
        ]);

        $validated['activo']   = $request->boolean('activo', true);
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Formulario editar usuario
     */
    public function edit(User $usuario)
    {
        $roles = User::ROLES;

        $empleados = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->get();

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'empleados'));
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password'    => 'nullable|string|min:8|confirmed',
            'role'        => ['required', Rule::in(array_keys(User::ROLES))],
            'activo'      => 'nullable|boolean',
            'empleado_id' => 'nullable|exists:empleados,id', // ✅ Debe validar que existe
        ]);

        $validated['activo'] = $request->boolean('activo');

        // Solo actualizar password si se proporcionó
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // ✅ DEBUG: Ver qué se está guardando
        \Log::info('Actualizando usuario', [
            'usuario_id' => $usuario->id,
            'datos' => $validated,
        ]);

        $usuario->update($validated);

        // ✅ DEBUG: Verificar después del update
        \Log::info('Usuario después de update', [
            'usuario_id' => $usuario->id,
            'empleado_id' => $usuario->empleado_id,
            'existe_en_bd' => User::find($usuario->id) ? 'SI' : 'NO',
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function destroy(User $usuario)
    {
        // No permitir eliminar el propio usuario
        if ($usuario->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'No puedes eliminar tu propio usuario');
        }

        // No permitir eliminar el único admin
        if ($usuario->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()
                ->with('error', 'No puedes eliminar el único administrador del sistema');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }

    /**
     * Activar / Desactivar usuario
     */
    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'No puedes desactivar tu propio usuario');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';

        return redirect()->back()
            ->with('success', "Usuario {$estado} exitosamente");
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restore($id)
    {
        $usuario = User::withTrashed()->findOrFail($id);
        $usuario->restore();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario restaurado exitosamente');
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword(Request $request, User $usuario)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $usuario->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Contraseña restablecida exitosamente');
    }
}