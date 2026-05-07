<?php

namespace App\Policies;

use App\Models\ContentSubmission;
use App\Models\User;

class ContentSubmissionPolicy
{
    public function create(User $user): bool
    {
        return true; // any authenticated user may submit
    }

    public function moderate(User $user): bool
    {
        return $user->is_admin;
    }
}
