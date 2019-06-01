<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 * @package App
 */
class Status extends Model
{

    /**
     * @var string
     */
    protected $table = "statuses";

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = ["status_code", "description", "created_at",
        "status_category_id"];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_category()
    {
        return $this->belongsTo('App\StatusCategory');
    }

    /**
     * @param int $id
     * @return mixed|string
     */
    public static function getDescription(int $id)
    {
        $status = "Unknown";
        $static = self::query()->findOrFail($id, ['description']);
        if ($static) {
            return $static->description;
        }
        return $status;
    }

}