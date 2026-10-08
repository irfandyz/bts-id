<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultUser = User::first();
        $userName = $defaultUser ? ($defaultUser->name ?: $defaultUser->username) : 'Jhon Doe';
        $userId = $defaultUser ? (string) $defaultUser->id : '1';

        $products = [
            [
                'title' => 'Awesome T-Shirt',
                'price' => 99.99,
                'description' => 'High-quality cotton t-shirt with classic crew neckline and breathable fabric.',
                'category' => 'Clothes',
                'images' => [
                    'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Slim Fit Denim Jeans',
                'price' => 129.50,
                'description' => 'Comfort stretch slim fit denim jeans in indigo wash.',
                'category' => 'Clothes',
                'images' => [
                    'https://images.unsplash.com/photo-1542272604-780c96856592?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Classic Leather Jacket',
                'price' => 299.00,
                'description' => 'Genuine black leather biker jacket with asymmetrical zipper and metal hardware.',
                'category' => 'Clothes',
                'images' => [
                    'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Urban Running Sneakers',
                'price' => 149.99,
                'description' => 'Lightweight athletic sneakers designed for marathon runners and casual wear.',
                'category' => 'Shoes',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Oxford Formal Leather Shoes',
                'price' => 189.00,
                'description' => 'Handcrafted brown leather oxford dress shoes with wooden heel.',
                'category' => 'Shoes',
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Wireless Noise-Canceling Headphones',
                'price' => 249.99,
                'description' => 'Over-ear Bluetooth headphones with active noise cancellation and 40h battery life.',
                'category' => 'Electronics',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Mechanical Gaming Keyboard',
                'price' => 119.00,
                'description' => 'RGB backlit mechanical keyboard with hot-swappable switches and aluminum frame.',
                'category' => 'Electronics',
                'images' => [
                    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Minimalist Smart Watch',
                'price' => 199.95,
                'description' => 'Sleek OLED smartwatch with heart-rate monitoring, GPS tracking, and water resistance.',
                'category' => 'Electronics',
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Vintage Sunglasses',
                'price' => 59.99,
                'description' => 'Polarized UV400 protection vintage retro sunglasses.',
                'category' => 'Accessories',
                'images' => [
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
            [
                'title' => 'Ergonomic Mesh Office Chair',
                'price' => 349.00,
                'description' => 'Breathable mesh executive desk chair with adjustable lumbar support and 3D armrests.',
                'category' => 'Furniture',
                'images' => [
                    'https://images.unsplash.com/photo-1580481077195-c9569b919d3f?auto=format&fit=crop&w=600&q=80',
                ],
                'created_by' => $userName,
                'created_by_id' => $userId,
                'updated_by' => $userName,
                'updated_by_id' => $userId,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['title' => $product['title']],
                $product
            );
        }
    }
}
