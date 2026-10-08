<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::create([
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'description' => 'High-quality cotton t-shirt',
            'category' => 'Clothes',
            'images' => ['https://placeimg.com/640/480/any'],
            'created_by' => 'Jhon Doe',
            'created_by_id' => '1',
            'updated_by' => 'Jhon Doe',
            'updated_by_id' => '1',
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'price',
                        'description',
                        'category',
                        'images',
                        'created_at',
                        'created_by',
                        'created_by_id',
                        'updated_at',
                        'updated_by',
                        'updated_by_id',
                    ],
                ],
            ]);
    }

    public function test_can_filter_products_by_search_and_category_and_pagination(): void
    {
        Product::create([
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'description' => 'High-quality cotton t-shirt',
            'category' => 'Clothes',
            'images' => ['https://placeimg.com/640/480/any'],
        ]);

        Product::create([
            'title' => 'Leather Shoes',
            'price' => 199.99,
            'description' => 'Formal leather shoes',
            'category' => 'Shoes',
            'images' => ['https://placeimg.com/640/480/shoes'],
        ]);

        // Filter search
        $searchResponse = $this->getJson('/api/products?search=T-Shirt');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Awesome T-Shirt');

        // Filter category
        $categoryResponse = $this->getJson('/api/products?category=Shoes');
        $categoryResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Leather Shoes');

        // Pagination
        $paginationResponse = $this->getJson('/api/products?limit=1&page=2');
        $paginationResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_get_single_product_detail(): void
    {
        $product = Product::create([
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'description' => 'High-quality cotton t-shirt',
            'category' => 'Clothes',
            'images' => ['https://placeimg.com/640/480/any'],
            'created_by' => 'Jhon Doe',
            'created_by_id' => '1',
            'updated_by' => 'Jhon Doe',
            'updated_by_id' => '1',
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'title' => 'Awesome T-Shirt',
                    'price' => 99.99,
                    'description' => 'High-quality cotton t-shirt',
                    'category' => 'Clothes',
                    'images' => ['https://placeimg.com/640/480/any'],
                    'created_by' => 'Jhon Doe',
                    'created_by_id' => '1',
                ],
            ]);
    }

    public function test_get_non_existent_product_returns_404_json(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }

    public function test_unauthenticated_user_cannot_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'title' => 'New Product',
            'price' => 50.0,
            'category' => 'Clothes',
            'images' => ['https://placeimg.com/640/480/any'],
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_product_with_audit_trail(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
            'name' => 'Jhon Doe',
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'description' => 'High-quality cotton t-shirt',
            'category' => 'Clothes',
            'images' => ['https://placeimg.com/640/480/any'],
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Awesome T-Shirt',
                    'price' => 99.99,
                    'category' => 'Clothes',
                    'created_by' => 'Jhon Doe',
                    'created_by_id' => (string) $user->id,
                    'updated_by' => 'Jhon Doe',
                    'updated_by_id' => (string) $user->id,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Awesome T-Shirt',
            'created_by_id' => (string) $user->id,
        ]);
    }

    public function test_authenticated_user_can_create_product_with_uploaded_file(): void
    {
        Storage::fake('public');

        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('shirt.jpg', 100, 'image/jpeg');

        $response = $this->post('/api/products', [
            'title' => 'Shirt with Uploaded Image',
            'price' => 50.0,
            'category' => 'Clothes',
            'images' => [$file],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertNotEmpty($data['images']);
        $this->assertStringContainsString('storage/products/', $data['images'][0]);
    }

    public function test_can_upload_image_via_upload_endpoint(): void
    {
        Storage::fake('public');

        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('photo.png', 100, 'image/png');

        $response = $this->post('/api/upload', [
            'image' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'url',
                    'urls',
                ],
            ]);
    }

    public function test_product_validation_failure_returns_400_with_errors(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        // Missing required fields
        $response = $this->postJson('/api/products', [
            'description' => 'Incomplete product',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'title',
                    'price',
                    'category',
                    'images',
                ],
            ]);
    }

    public function test_authenticated_user_can_update_product(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
            'name' => 'Jhon Doe',
        ]);

        Sanctum::actingAs($user);

        $product = Product::create([
            'title' => 'Old Title',
            'price' => 50.0,
            'category' => 'Clothes',
            'images' => ['https://placeimg.com/640/480/any'],
            'created_by' => 'Initial User',
            'created_by_id' => '2',
        ]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'title' => 'Updated Title',
            'price' => 75.5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'title' => 'Updated Title',
                    'price' => 75.5,
                    'updated_by' => 'Jhon Doe',
                    'updated_by_id' => (string) $user->id,
                ],
            ]);
    }

    public function test_update_non_existent_product_returns_404(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/products/99999', [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }

    public function test_authenticated_user_can_delete_product(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        $product = Product::create([
            'title' => 'Product To Delete',
            'price' => 10.0,
            'category' => 'Toys',
            'images' => ['https://placeimg.com/640/480/any'],
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_delete_product_cleans_up_local_storage_images(): void
    {
        Storage::fake('public');

        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('item.png', 100, 'image/png');
        $storedPath = $file->store('products', 'public');
        $fullUrl = url('storage/'.$storedPath);

        Storage::disk('public')->assertExists($storedPath);

        $product = Product::create([
            'title' => 'Product With Local Image',
            'price' => 15.0,
            'category' => 'Gadgets',
            'images' => [$fullUrl],
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_delete_non_existent_product_returns_404(): void
    {
        $user = User::create([
            'username' => 'jhon_doe',
            'password' => bcrypt('supersecret'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/products/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }
}
