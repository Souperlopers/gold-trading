<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    protected static bool $ownerCreated = false;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isOwner = !static::$ownerCreated;

        if ($isOwner) {
            static::$ownerCreated = true;
        }

        $role = $isOwner
            ? User::ROLES['0']
            : fake()->randomElement(array_slice(User::ROLES, 1));

        return [
            'name'        => fake()->name(),
            'password'    => static::$password ??= Hash::make('password'),
            'phone'       => fake()->unique()->numerify('09#########'),
            'national_id' => fake()->unique()->numerify('##########'),
            'role'        => $role,
            'approved_by' => null,
            'approved_at' => match ($role) {
                'owner', 'viewer' => null,
                'admin', 'trader' => fake()->dateTime(),
            },
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // If the user is an admin, approved_by should be the owner
            if ($user->role === 'admin') {
                $owner = User::where('role', 'owner')->first();
                if ($owner && $owner->id !== $user->id) {
                    $user->approved_by = $owner->id;
                    $user->save();
                }
            }

            // If the user is a trader, approved_by should be an owner or admin
            if ($user->role === 'trader') {
                $approver = User::query()->where('role', 'owner')->orWhere('role', 'admin')
                    ->inRandomOrder()->first();

                if ($approver) {
                    $user->approved_by = $approver->id;
                    $user->save();
                }
            }
        });
    }


    /**
     * state methods
     */

    public function owner(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'owner',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
            'approved_at' => now(),
            'approved_by' => User::where('role', 'owner')->first()?->id,
        ]);
    }

    public function trader(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'trader',
            'approved_at' => now(),
            'approved_by' => fake()->randomElement(
                User::where('role', ['owner', 'admin'])->pluck('id')->toArray()
            ),
        ]);
    }

    public function viewer(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'viewer',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'approved_at' => now(),
        ]);
    }

    public function unapproved(): static
    {
        return $this->state(fn(array $attributes) => [
            'approved_at' => null,
        ]);
    }
}
