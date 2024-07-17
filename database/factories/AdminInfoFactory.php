<?php

namespace Database\Factories;

use App\Models\AdminInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminInfo>
 */
class AdminInfoFactory extends Factory
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
            'admin_name' => $this->faker->name(),
            'admin_hash' => bcrypt('password'),
            'email' => $this->faker->email(),
            'status' => 1,
            'create_time' => time()
        ];
    }
}
