<?php

namespace App\Policies;

use App\Models\Classified;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassifiedPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Classified $classified): bool
    {
        // Published classifieds are viewable by anyone
        if ($classified->status === 'published') {
            return true;
        }
        // Only the owner can view draft/sold classifieds
        return $user && $user->id === $classified->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Classified $classified): bool
    {
        return $user->id === $classified->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Classified $classified): bool
    {
        return $user->id === $classified->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Classified $classified): bool
    {
        return $user->id === $classified->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Classified $classified): bool
    {
        return $user->id === $classified->user_id;
    }
}
