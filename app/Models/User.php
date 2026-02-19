<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'activo',
        'empleado_id',
        'ultimo_acceso',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acceso'     => 'datetime',
        'activo'            => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Roles ─────────────────────────────────────────────────────
    const ROLES = [
        'admin'      => 'Administrador',
        'rrhh'       => 'Recursos Humanos',
        'contador'   => 'Contador',
        'supervisor' => 'Supervisor',
        'consulta'   => 'Solo Consulta',
    ];

    const ROLE_COLORS = [
        'admin'      => 'red',
        'rrhh'       => 'blue',
        'contador'   => 'green',
        'supervisor' => 'yellow',
        'consulta'   => 'gray',
    ];

    // ── Relaciones ────────────────────────────────────────────────
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Nomina\Models\Empleado::class, 'empleado_id');
    }

    // ── Accessors ─────────────────────────────────────────────────
    public function getRoleNombreAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getRoleColorAttribute(): string
    {
        return self::ROLE_COLORS[$this->role] ?? 'gray';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Generar avatar con iniciales
        $iniciales = collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn($p) => strtoupper(substr($p, 0, 1)))
            ->join('');

        return "https://ui-avatars.com/api/?name={$iniciales}&background=1e40af&color=fff&size=128";
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRRHH(): bool
    {
        return in_array($this->role, ['admin', 'rrhh']);
    }

    public function isContador(): bool
    {
        return in_array($this->role, ['admin', 'contador']);
    }

    public function isSupervisor(): bool
    {
        return in_array($this->role, ['admin', 'supervisor']);
    }

    public function canEdit(): bool
    {
        return in_array($this->role, ['admin', 'rrhh']);
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorRol($query, string $role)
    {
        return $query->where('role', $role);
    }
}