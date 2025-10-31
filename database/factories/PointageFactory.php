<?php

namespace Database\Factories;

use App\Models\Employe;
use App\Models\Badge;
use App\Models\Portique;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointageFactory extends Factory
{
    public function definition(): array
    {
        $employe = Employe::inRandomOrder()->first() ?? Employe::factory()->create();
        $badge = Badge::where('employe_id', $employe->id)->inRandomOrder()->first() ?? Badge::factory()->create(['employe_id' => $employe->id]);
        $portique = Portique::inRandomOrder()->first() ?? Portique::factory()->create();

        return [
            'employe_id' => $employe->id,
            'badge_id' => $badge->id,
            'portique_id' => $portique->id,
            'date_heure' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'type' => $this->faker->randomElement(['entrée', 'sortie']),
            'source' => $badge->type,
        ];
    }
}
