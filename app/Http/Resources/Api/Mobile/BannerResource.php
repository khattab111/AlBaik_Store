<?php

namespace App\Http\Resources\Api\Mobile;

use App\Http\Resources\Api\Mobile\Concerns\FormatsMobileValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    use FormatsMobileValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized($this->resource, 'title'),
            'subtitle' => $this->localized($this->resource, 'subtitle'),
            'eyebrow' => $this->localized($this->resource, 'eyebrow'),
            'image' => $this->imageUrl($this->image),
            'url' => $this->url,
            'secondary_url' => $this->secondary_url,
            'primary_button_text' => $this->localized($this->resource, 'primary_button_text'),
            'secondary_button_text' => $this->localized($this->resource, 'secondary_button_text'),
            'placement' => $this->placement,
        ];
    }
}
