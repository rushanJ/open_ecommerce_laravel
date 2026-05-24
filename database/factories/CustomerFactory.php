<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->optional()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_path' => null,
            'dob' => null,
            'gender' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => null,
            'last_login_at' => null,
            'accepts_marketing' => false,
            'metadata' => null,
            'remember_token' => Str::random(10),
        ];
    }
}
