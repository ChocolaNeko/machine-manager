<?php

namespace Database\Factories;

use App\Models\AdminInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminInfo>
 */
class AdminFactory extends Factory
{
    protected $model = AdminInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->email(),
            'password' => $this->faker->password('password')
        ];
    }
}
