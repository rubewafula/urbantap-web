<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'business_description' => $this->business_description,
            'work_phone_no' => $this->work_phone_no,
            'work_location' => $this->work_location,
            'work_lat' => $this->work_lat,
            'work_lng' => $this->work_lng,
            'service_provider' => $this->serviceProvider,
        ];
    }
}
