<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed reviewer
 */
class Review extends Model
{
    public function reviewer() {
        return $this->belongsTo('App\User','reviewer_id');
    }

    public function toArray() {
        $data = parent::toArray();
        $data['reviewer'] = optional($this->reviewer)->name;
        return $data;
    }
}
