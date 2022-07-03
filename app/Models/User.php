<?php

namespace App\Models;

use Core\Database\Model as BaseModel;

class User extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'users';
}
