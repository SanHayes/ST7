<?php

namespace App\Policies;

class UserRealPolicy
{
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
