<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use  App\User;

class UserTelephone implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
          preg_match("/^(?:\+?254|0)?(7\d{8})/", $value, $matches);
          if(empty($matches)){
              return FALSE;
          }
          $phone = '254' . $matches[1];

          if(User::where('phone_no',$phone)->exists())
          {

            return FALSE;
          } else{

            return  TRUE;
          }




    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The telephone number already exists,Please login ';
    }
}
