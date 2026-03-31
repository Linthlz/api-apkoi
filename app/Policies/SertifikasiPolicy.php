<?php

namespace App\Policies;

use App\Models\Sertifikasi;
use App\Models\User;

class SertifikasiPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function update(User $user, Sertifikasi $sertifikasi)
    {
        return $user->id === $sertifikasi->publisher_id;
    }

    public function delete(User $user, Sertifikasi $sertifikasi)
    {
        return $user->id === $sertifikasi->publisher_id;
    }
}
