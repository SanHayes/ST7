<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create()
    {
        return false;
    }
    public function update()
    {
        return true;
    }
    public function view()
    {
        return true;
    }
}
