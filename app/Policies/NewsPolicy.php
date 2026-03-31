<?php

namespace App\Policies;

use App\Models\User;
use App\Models\News;

class NewsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function update(User $user, News $news)
    {
        return $user->id === $news->publisher_id;
    }

    public function delete(User $user, News $news)
    {
        return $user->id === $news->publisher_id;
    }
}
