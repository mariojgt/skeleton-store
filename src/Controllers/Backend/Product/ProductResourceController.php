<?php

namespace Skeleton\Store\Controllers\Backend\Product;

use Illuminate\Http\Request;
use Skeleton\Store\Models\Product;
use App\Http\Controllers\Controller;
use Skeleton\Store\Models\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ProductResourceController extends Controller
{
    /**
     * Display all resources for a product
     */
    public function index(Product $product): JsonResponse
    {
        $resources = $product->resources()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $resources]);
    }

    /**
     * Store a new resource
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $this->validateResource($request);
        $resource = $product->resources()->create($validated);

        return response()->json(['data' => $resource], 201);
    }

    /**
     * Update an existing resource
     */
    public function update(Request $request, Product $product, ProductResource $resource): JsonResponse
    {
        if (!$this->belongsToProduct($product, $resource)) {
            return $this->resourceNotFoundResponse();
        }

        $validated = $this->validateResource($request);
        $resource->update($validated);

        return response()->json(['data' => $resource]);
    }

    /**
     * Delete a resource
     */
    public function destroy(Product $product, ProductResource $resource): JsonResponse
    {
        if (!$this->belongsToProduct($product, $resource)) {
            return $this->resourceNotFoundResponse();
        }

        $resource->delete();

        return response()->json(['message' => 'Resource deleted successfully']);
    }

    /**
     * Validate resource request
     */
    private function validateResource(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'required|string|in:link,file',
            'resource_url' => 'nullable|url',
            'file_path' => 'nullable|string'
        ]);
    }

    /**
     * Check if resource belongs to product
     */
    private function belongsToProduct(Product $product, ProductResource $resource): bool
    {
        return $resource->product_id === $product->id;
    }

    /**
     * Resource not found response
     */
    private function resourceNotFoundResponse(): JsonResponse
    {
        return response()->json(['message' => 'Resource not found.'], 404);
    }
}
