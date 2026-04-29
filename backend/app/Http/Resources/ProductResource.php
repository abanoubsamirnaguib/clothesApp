<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = (float) $this->price;
        $salePrice = $this->sale_price !== null ? (float) $this->sale_price : null;

        return [
            'id' => $this->id,
            'databaseId' => $this->id,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $price,
            'sale_price' => $salePrice,
            'regularPrice' => '$'.number_format($price, 2),
            'salePrice' => $salePrice ? '$'.number_format($salePrice, 2) : null,
            'stock_quantity' => $this->stock_quantity,
            'status' => $this->status,
            'color' => $this->color,
            'style' => $this->style,
            'sizes' => $this->sizes ?? [],
            'featured_image' => $this->featured_image,
            'images' => $this->images ?? [],
            'image' => ['sourceUrl' => $this->featured_image],
            'galleryImages' => [
                'nodes' => collect($this->images ?? [])->skip(1)->map(fn ($url) => ['sourceUrl' => $url])->values(),
            ],
            'allPaStyle' => ['nodes' => [['name' => $this->style]]],
        ];
    }
}
