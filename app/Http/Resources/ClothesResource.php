<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClothesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'name' => $this->name,
            'material' => $this->material,
            'type' => $this->type,
            'colorway' => $this->colorway,
            'washingInstructions' => $this->washing_instructions,
            'temperature' => $this->temperature,
            'isInLaundry' => $this->is_in_laundry,
            'picture' => $this->picture,
        ];
    }
}
