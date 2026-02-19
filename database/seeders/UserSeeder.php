<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creando usuarios del sistema...');

        $usuarios = [
            [
                'name'               => 'Administrador Sistema',
                'email'              => 'admin@nomina.com',
                'password'           => Hash::make('password'),
                'role'               => 'admin',
                'activo'             => true,
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Recursos Humanos',
                'email'              => 'rrhh@nomina.com',
                'password'           => Hash::make('password'),
                'role'               => 'rrhh',
                'activo'             => true,
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Contador Principal',
                'email'              => 'contabilidad@nomina.com',
                'password'           => Hash::make('password'),
                'role'               => 'contador',
                'activo'             => true,
                'email_verified_at'  => now(),
            ],
            [
                'name'               => 'Supervisor Nómina',
                'email'              => 'supervisor@nomina.com',
                'password'           => Hash::make('password'),
                'role'               => 'supervisor',
                'activo'             => true,
                'email_verified_at'  => now(),
            ],
        ];

        foreach ($usuarios as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            $this->command->line(
                "   ✅ {$user->name} ({$user->role}) → {$user->email}"
            );
        }

        $this->command->newLine();
        $this->command->info('✅ Usuarios creados: ' . User::count());
        $this->command->newLine();
        $this->command->line('🔑 Credenciales:');
        $this->command->line('   admin@nomina.com        → password  (Administrador)');
        $this->command->line('   rrhh@nomina.com         → password  (Recursos Humanos)');
        $this->command->line('   contabilidad@nomina.com → password  (Contador)');
        $this->command->line('   supervisor@nomina.com   → password  (Supervisor)');
    }
}