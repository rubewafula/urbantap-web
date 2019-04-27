<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
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
            'business_name' => $this->business_name,
            'description' => $this->description,
            'location' => $this->location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'phone_no' => $this->phone_no,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'service_provider' => $this->serviceProvider,
        ];
    }
}
