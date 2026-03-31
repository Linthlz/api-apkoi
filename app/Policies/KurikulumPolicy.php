<?php

namespace App\Policies;

use App\Models\Kurikulum;
use App\Models\User;

class KurikulumPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
     public function update(User $user, Kurikulum $kuriukulum)
    {
        return $user->id === $kuriukulum->publisher_id;
    }

    public function delete(User $user, Kurikulum $kuriukulum)
    {
        return $user->id === $kuriukulum->publisher_id;
    }
}
