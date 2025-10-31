<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->randomElement([
                'Informatique', 'Comptabilité', 'Ressources Humaines', 'Commercial', 'Production'
            ]),
        ];
    }
}
