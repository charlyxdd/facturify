<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    public function view(User $user, Thread $thread): bool
    {
        return $thread->participants->contains($user);
    }

    public function update(User $user, Thread $thread): bool
    {
        return $user->id === $thread->created_by;
    }

    public function delete(User $user, Thread $thread): bool
    {
        return $user->id === $thread->created_by;
    }
}
