<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filtering, searching, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $limit = $request->integer('limit', 10);
        $page = $request->integer('page', 1);

        $cacheVersion = Cache::get('products_cache_version', 1);
        $cacheKey = "products:v{$cacheVersion}:s={$search}:c={$category}:l={$limit}:p={$page}";

        $result = Cache::remember($cacheKey, 60, function () use ($search, $category, $limit) {
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

            $paginated = $query->latest('id')->paginate($limit);

            return [
                'items' => ProductResource::collection($paginated)->resolve(),
                ...$this->formatPagination($paginated),
            ];
        });

        return $this->success($result['items'], 'Products retrieved successfully', 200, [
            'links' => $result['links'],
            'meta' => $result['meta'],
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string|int $id): JsonResponse
    {
        $cacheKey = "product_detail_{$id}";

        $productData = Cache::remember($cacheKey, 60, function () use ($id) {
            $product = Product::find($id);

            return $product ? (new ProductResource($product))->resolve() : null;
        });

        if (! $productData) {
            return $this->error('Product not found', 404);
        }

        return $this->success($productData, 'Product retrieved successfully');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['images'] = $this->handleImages($request);

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

        return $this->success(
            (new ProductResource($product))->resolve(),
            'Product created successfully',
            201
        );
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, string|int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->error('Product not found', 404);
        }

        $validated = $request->validated();

        if ($request->has('images') || $request->hasFile('images')) {
            $validated['images'] = $this->handleImages($request);
        }

        $user = $request->user();

        $userName = $user?->name ?: ($user?->username ?: 'System');
        $userId = (string) ($user?->id ?? '');

        $product->update([
            ...$validated,
            'updated_by' => $userName,
            'updated_by_id' => $userId,
        ]);

        $this->invalidateProductCache($product->id);

        return $this->success(
            (new ProductResource($product->fresh()))->resolve(),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(string|int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->error('Product not found', 404);
        }

        // Clean up locally stored image files from disk if any
        if (is_array($product->images)) {
            foreach ($product->images as $imageUrl) {
                if (is_string($imageUrl) && str_contains($imageUrl, '/storage/products/')) {
                    $fileName = basename(parse_url($imageUrl, PHP_URL_PATH));
                    if ($fileName && Storage::disk('public')->exists('products/'.$fileName)) {
                        Storage::disk('public')->delete('products/'.$fileName);
                    }
                }
            }
        }

        $product->delete();

        $this->invalidateProductCache($id);

        return $this->success(null, 'Product deleted successfully');
    }

    /**
     * Upload single or multiple images endpoint.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required_without:images', 'file', 'image', 'max:5120'],
            'images' => ['required_without:image', 'array'],
            'images.*' => ['file', 'image', 'max:5120'],
        ]);

        $urls = [];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $urls[] = url('storage/'.$path);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $urls[] = url('storage/'.$path);
            }
        }

        return $this->success([
            'url' => $urls[0] ?? null,
            'urls' => $urls,
        ], 'Image(s) uploaded successfully', 201);
    }

    /**
     * Handle both URL strings and UploadedFile instances (supports mixed arrays).
     *
     * @return array<string>
     */
    private function handleImages(Request $request): array
    {
        $processed = [];
        $textImages = (array) $request->input('images', []);
        $fileImages = (array) $request->file('images', []);
        $rawImages = array_merge($textImages, $fileImages);

        foreach ($rawImages as $item) {
            if ($item instanceof UploadedFile) {
                $path = $item->store('products', 'public');
                $processed[] = url('storage/'.$path);
            } elseif (is_string($item) && ! empty($item)) {
                $processed[] = $item;
            }
        }

        return $processed;
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
