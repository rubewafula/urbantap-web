<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusCategory extends Model
{
    
   protected $table = "status_categories";

   protected $primaryKey = 'id';
   
   protected $fillable = ["category_code", "description", "created_at"];

}