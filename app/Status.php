<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    
    protected $table = "statuses";

    protected $primaryKey = 'id';

    protected $fillable = ["status_code", "description", "created_at", 
                "status_category_id"];

    public function status_category()
    {
        return $this->belongsTo('App\StatusCategory');
    }

}