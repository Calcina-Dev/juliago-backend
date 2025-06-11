<?php

namespace Database\Factories;

use App\Models\HistorialPedido;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialPedidoFactory extends Factory
{
    protected $model = HistorialPedido::class;

    public function definition(): array
    {
        $estados = ['pendiente', 'en_proceso', 'servido', 'pagado', 'cerrado'];
        $estadoAnterior = $this->faker->randomElement($estados);
        $estadoNuevo = $this->faker->randomElement(array_diff($estados, [$estadoAnterior]));

        return [
            'pedido_id' => Pedido::factory(),
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'usuario_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
