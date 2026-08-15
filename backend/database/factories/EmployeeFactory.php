<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_code' => $this->faker->unique()->bothify('EMP-#####'),
            'phone' => $this->faker->phoneNumber(),
            'position' => $this->faker->jobTitle(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}