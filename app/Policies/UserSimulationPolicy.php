<?php

namespace App\Policies;

class UserSimulationPolicy
{
    public function delete()
    {
        return true;
    }
}
