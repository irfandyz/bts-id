<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filtering, searching, and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $limit = $request->integer('limit', 10);
        $page = $request->integer('page', 1);

        $cacheVersion = Cache::get('products_cache_version', 1);
        $cacheKey = "products:v{$cacheVersion}:s={$search}:c={$category}:l={$limit}:p={$page}";

        $products = Cache::remember($cacheKey, 60, function () use ($search, $category, $limit) {
            $query = Product::query();

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if (! empty($category)) {
                $query->where('category', $category);
            }

            return $query->latest('id')->paginate($limit);
        });

        return ProductResource::collection($products);
    }

    /**
     * Display the specified product.
     */
    public function show(string|int $id): JsonResponse|ProductResource
    {
        $cacheKey = "product_detail_{$id}";

        $product = Cache::remember($cacheKey, 60, function () use ($id) {
            return Product::find($id);
        });

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        return new ProductResource($product);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $userName = $user?->name ?: ($user?->username ?: 'System');
        $userId = (string) ($user?->id ?? '');

        $product = Product::create([
            ...$validated,
            'created_by' => $userName,
            'created_by_id' => $userId,
            'updated_by' => $userName,
            'updated_by_id' => $userId,
        ]);

        $this->invalidateProductCache($product->id);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, string|int $id): JsonResponse|ProductResource
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $validated = $request->validated();
        $user = $request->user();

        $userName = $user?->name ?: ($user?->username ?: 'System');
        $userId = (string) ($user?->id ?? '');

        $product->update([
            ...$validated,
            'updated_by' => $userName,
            'updated_by_id' => $userId,
        ]);

        $this->invalidateProductCache($product->id);

        return new ProductResource($product->fresh());
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(string|int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        $this->invalidateProductCache($id);

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }

    /**
     * Invalidate product caching.
     */
    private function invalidateProductCache(string|int $id): void
    {
        Cache::forget("product_detail_{$id}");
        Cache::increment('products_cache_version');
    }
}
