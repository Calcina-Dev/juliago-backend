<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'mesa_id' => Mesa::inRandomOrder()->first()?->id ?? Mesa::factory(),
            'estado' => 'pendiente',
            'total' => $this->faker->randomFloat(2, 10, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
