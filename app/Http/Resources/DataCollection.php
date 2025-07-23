<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

class DataCollection extends ResourceCollection
{
    protected mixed $resourceClass;

    public function setResourceClass(mixed $resourceClass): void
    {
        $this->resourceClass = $resourceClass;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resourceClass = $this->resourceClass;
        return [
            'data' => $resourceClass::collection($this->collection)->toArray($request),
            'current_page' => $this->currentPage(),
            'has_more_pages' => $this->hasMorePages(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'total' => $this->total(),
        ];
    }
}
