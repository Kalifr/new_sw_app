<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and components',
                'meta_title' => 'Electronics - Buy Quality Electronic Products',
                'meta_description' => 'Browse our wide selection of electronic products from verified suppliers.',
                'meta_keywords' => 'electronics, gadgets, devices, technology',
            ],
            [
                'name' => 'Machinery',
                'description' => 'Industrial machinery and equipment',
                'meta_title' => 'Industrial Machinery and Equipment',
                'meta_description' => 'Find reliable industrial machinery and equipment from trusted manufacturers.',
                'meta_keywords' => 'machinery, industrial equipment, manufacturing',
            ],
            [
                'name' => 'Textiles',
                'description' => 'Fabrics and textile products',
                'meta_title' => 'Textile Products and Materials',
                'meta_description' => 'Source quality textiles and fabric products from global suppliers.',
                'meta_keywords' => 'textiles, fabrics, clothing materials',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'meta_title' => $category['meta_title'],
                    'meta_description' => $category['meta_description'],
                    'meta_keywords' => $category['meta_keywords'],
                ]
            );
        }
    }
}
