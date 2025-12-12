<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class MessagePolicy
{
    public function create(User $user, Thread $thread): bool
    {
        return $thread->participants->contains($user);
    }
}
