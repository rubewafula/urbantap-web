<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Utilities\Utils;

class UserPersonalDetail extends Model
{


	protected $cast = [
		'passport_photo' => "json"
	]; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
   

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }


    public function getPassportPhotoAttribute($value){
    	return Utils::PROFILE_URL . array_get($value, 'media_url', 'default-avatar.jpg');
    }





}
