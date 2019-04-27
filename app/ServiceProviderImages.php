<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceProviderImages extends Model
{
    public function toArray() {
//        $data = parent::toArray();
        $data['image'] = url($this->image);
        return $data;
    }
}
