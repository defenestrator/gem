<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Animal $animal): bool
    {
        if ($animal->status === 'published') {
            return true;
        }
        return $user && $user->id === $animal->user_id;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Animal $animal): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Animal $animal): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, Animal $animal): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, Animal $animal): bool
    {
        return $user->is_admin;
    }
}
