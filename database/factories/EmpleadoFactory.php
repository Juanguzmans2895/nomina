<?php

namespace Database\Factories;

use App\Modules\Nomina\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $genero = $this->faker->randomElement(['M', 'F']);
        $fechaNacimiento = $this->faker->dateTimeBetween('-65 years', '-18 years');
        $fechaIngreso = $this->faker->dateTimeBetween('-10 years', 'now');

        return [
            // Datos Personales
            'tipo_documento' => $this->faker->randomElement(['CC', 'CE']),
            'numero_documento' => $this->faker->unique()->numerify('##########'),
            'primer_nombre' => $this->faker->firstName($genero === 'M' ? 'male' : 'female'),
            'segundo_nombre' => $this->faker->optional(0.7)->firstName($genero === 'M' ? 'male' : 'female'),
            'primer_apellido' => $this->faker->lastName(),
            'segundo_apellido' => $this->faker->optional(0.9)->lastName(),
            'fecha_nacimiento' => $fechaNacimiento,
            'genero' => $genero,
            'estado_civil' => $this->faker->randomElement(['soltero', 'casado', 'union_libre', 'viudo', 'divorciado']),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->optional(0.6)->numerify('6#######'),
            'celular' => $this->faker->numerify('3#########'),
            'direccion' => $this->faker->address(),
            'ciudad' => $this->faker->city(),
            'departamento' => $this->faker->randomElement([
                'Bogotá D.C.', 'Antioquia', 'Valle del Cauca', 'Santander', 
                'Atlántico', 'Cundinamarca', 'Tolima', 'Boyacá'
            ]),
            
            // Datos Laborales
            'codigo_empleado' => $this->faker->unique()->numerify('EMP-####'),
            'fecha_ingreso' => $fechaIngreso,
            'fecha_retiro' => null,
            'tipo_contrato' => $this->faker->randomElement(['indefinido', 'fijo', 'obra_labor']),
            'cargo' => $this->faker->randomElement([
                'Gerente', 'Analista', 'Coordinador', 'Asistente', 'Auxiliar',
                'Jefe de Área', 'Especialista', 'Técnico', 'Profesional', 'Consultor'
            ]),
            'dependencia' => $this->faker->randomElement([
                'Recursos Humanos', 'Contabilidad', 'Sistemas', 'Administración',
                'Ventas', 'Compras', 'Producción', 'Calidad', 'Logística'
            ]),
            'salario_basico' => $this->faker->randomElement([
                1300000,  // SMLV
                2000000,
                2500000,
                3000000,
                3500000,
                4000000,
                5000000,
                6000000,
                8000000,
                10000000,
            ]),
            'estado' => 'activo',
            
            // Datos Bancarios
            'banco' => $this->faker->randomElement([
                'Bancolombia', 'Banco de Bogotá', 'Davivienda', 'BBVA', 
                'Banco Popular', 'Banco Occidente', 'Banco Agrario', 'Colpatria'
            ]),
            'tipo_cuenta' => $this->faker->randomElement(['ahorros', 'corriente']),
            'numero_cuenta' => $this->faker->numerify('##########'),
            
            // Seguridad Social
            'eps' => $this->faker->randomElement([
                'Sura', 'Sanitas', 'Compensar', 'Salud Total', 
                'Nueva EPS', 'Famisanar', 'Cafesalud', 'Coomeva'
            ]),
            'eps_codigo' => $this->faker->numerify('EPS###'),
            'fondo_pension' => $this->faker->randomElement([
                'Protección', 'Porvenir', 'Colfondos', 'Skandia', 'Old Mutual'
            ]),
            'pension_codigo' => $this->faker->numerify('PEN###'),
            'arl' => $this->faker->randomElement([
                'Sura', 'Positiva', 'Colmena', 'Liberty', 'Equidad'
            ]),
            'arl_codigo' => $this->faker->numerify('ARL###'),
            'caja_compensacion' => $this->faker->randomElement([
                'Compensar', 'Colsubsidio', 'Cafam', 'Comfenalco', 'Comfandi'
            ]),
            'caja_codigo' => $this->faker->numerify('CAJA##'),
            'clase_riesgo' => $this->faker->randomElement([
                0.522,  // Clase I
                1.044,  // Clase II
                2.436,  // Clase III
                4.350,  // Clase IV
                6.960,  // Clase V
            ]),
            
            // Otros
            'exento_retencion' => false,
            'calcula_auxilio_transporte' => true,
            
            // Auditoría
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    /**
     * Empleado retirado
     */
    public function retirado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'retirado',
            'fecha_retiro' => $this->faker->dateTimeBetween($attributes['fecha_ingreso'], 'now'),
        ]);
    }

    /**
     * Empleado inactivo
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'inactivo',
        ]);
    }

    /**
     * Empleado con salario alto
     */
    public function salarioAlto(): static
    {
        return $this->state(fn (array $attributes) => [
            'salario_basico' => $this->faker->randomElement([
                10000000, 12000000, 15000000, 20000000, 25000000
            ]),
            'cargo' => $this->faker->randomElement([
                'Gerente General', 'Director', 'Vicepresidente', 'Gerente Senior'
            ]),
        ]);
    }

    /**
     * Empleado con SMLV
     */
    public function salarioMinimo(): static
    {
        return $this->state(fn (array $attributes) => [
            'salario_basico' => 1300000,
            'cargo' => $this->faker->randomElement([
                'Auxiliar', 'Asistente', 'Operario', 'Mensajero'
            ]),
        ]);
    }
}