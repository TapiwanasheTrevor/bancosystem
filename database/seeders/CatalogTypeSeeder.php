<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CatalogTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample Hire Purchase categories
        $hirePurchaseCategories = [
            'Electronics' => [
                'TVs & Home Theatres',
                'Laptops & Computers',
                'Smartphones & Tablets',
                'Audio Equipment'
            ],
            'Furniture' => [
                'Living Room',
                'Bedroom',
                'Dining Room',
                'Office'
            ],
            'Appliances' => [
                'Kitchen Appliances',
                'Laundry Machines',
                'Refrigeration',
                'Small Appliances'
            ],
            'Home Improvement' => [
                'Tools',
                'Building Materials',
                'Plumbing',
                'Electrical'
            ]
        ];

        // Create all the Hire Purchase categories
        foreach ($hirePurchaseCategories as $mainCategory => $subCategories) {
            $parentCategory = Category::create([
                'name' => $mainCategory,
                'catalog_type' => 'hirepurchase'
            ]);

            foreach ($subCategories as $subCategory) {
                Category::create([
                    'name' => $subCategory,
                    'parent_id' => $parentCategory->id,
                    'catalog_type' => 'hirepurchase'
                ]);
            }
        }

        // Create sample MicroBiz categories (if none exist)
        if (Category::where('catalog_type', 'microbiz')->count() === 0) {
            $microBizCategories = [
                'Business Equipment' => [
                    'POS Systems',
                    'Kitchen Equipment',
                    'Office Equipment',
                    'Manufacturing Tools'
                ],
                'Inventory Financing' => [
                    'Retail Products',
                    'Raw Materials',
                    'Wholesale Goods',
                    'Seasonal Stock'
                ],
                'Vehicles' => [
                    'Delivery Vans',
                    'Motorcycles',
                    'Trucks',
                    'Commercial Vehicles'
                ],
                'Professional Services' => [
                    'Accounting Software',
                    'Business Services',
                    'Training Programs',
                    'Marketing Solutions'
                ]
            ];

            // Create all the MicroBiz categories
            foreach ($microBizCategories as $mainCategory => $subCategories) {
                $parentCategory = Category::create([
                    'name' => $mainCategory,
                    'catalog_type' => 'microbiz'
                ]);

                foreach ($subCategories as $subCategory) {
                    Category::create([
                        'name' => $subCategory,
                        'parent_id' => $parentCategory->id,
                        'catalog_type' => 'microbiz'
                    ]);
                }
            }
        }
    }
}
