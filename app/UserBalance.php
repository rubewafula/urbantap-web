<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserBalance
 * @package App
 */
class UserBalance extends Model
{
    /**
     * @var string
     */
    protected $table = "user_balance";

    /**
     * @var array
     */
    protected $guarded = [];
}
