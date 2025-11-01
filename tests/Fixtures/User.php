<?php

namespace Bhhaskin\RolesPermissions\Tests\Fixtures;

use Bhhaskin\RolesPermissions\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;

    protected $guarded = [];
}
