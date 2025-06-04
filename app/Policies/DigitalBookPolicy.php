<?php

namespace App\Policies;

use App\Models\DigitalBook;
use App\Models\User;

class DigitalBookPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view books
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DigitalBook $digitalBook): bool
    {
        return $digitalBook->status === 'published' ||
               $user->isAdmin() ||
               $digitalBook->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canUploadBooks();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DigitalBook $digitalBook): bool
    {
        return $user->isAdmin() || $digitalBook->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DigitalBook $digitalBook): bool
    {
        return $user->isAdmin() || $digitalBook->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DigitalBook $digitalBook): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DigitalBook $digitalBook): bool
    {
        return $user->isAdmin();
    }
}
