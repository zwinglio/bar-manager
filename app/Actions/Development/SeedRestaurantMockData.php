<?php

namespace App\Actions\Development;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Support\Facades\Hash;

final class SeedRestaurantMockData
{
    /**
     * Categories keyed by name with their sort order.
     *
     * @var array<string, int>
     */
    private const CATEGORIES = [
        'Petiscos' => 1,
        'Bebidas' => 2,
        'Cervejas' => 3,
        'Drinks' => 4,
        'Pratos Principais' => 5,
        'Sobremesas' => 6,
    ];

    /**
     * Products grouped by category name, each item is
     * [name, description, price, cost].
     *
     * @var array<string, array<int, array{0: string, 1: string, 2: string, 3: string}>>
     */
    private const PRODUCTS = [
        'Petiscos' => [
            ['Batata Frita', 'Porção de batata frita crocante com tempero da casa', '28.90', '8.50'],
            ['Coxinha', 'Coxinha de frango com catupiry', '12.50', '4.20'],
            ['Isca de Peixe', 'Iscas de peixe empanadas com molho tártaro', '36.90', '12.00'],
        ],
        'Bebidas' => [
            ['Guaraná Antarctica', 'Lata 350ml', '7.00', '3.50'],
            ['Água com Gás', 'Garrafa 500ml', '5.00', '2.00'],
        ],
        'Cervejas' => [
            ['Chopp Brahma', 'Copo 400ml gelado', '12.00', '4.50'],
            ['Heineken Long Neck', 'Garrafa 330ml', '13.50', '6.00'],
        ],
        'Drinks' => [
            ['Caipirinha', 'Cachaça, limão, açúcar e gelo', '18.00', '5.00'],
            ['Gin Tônica', 'Gin, tônica e limão', '22.00', '7.00'],
        ],
        'Pratos Principais' => [
            ['Picanha na Chapa', 'Picanha 400g com arroz, farofa e vinagrete', '69.90', '28.00'],
            ['Filé à Parmegiana', 'Filé empanado com molho e queijo gratinado', '52.90', '18.00'],
        ],
        'Sobremesas' => [
            ['Pudim', 'Pudim de leite condensado', '14.90', '4.00'],
            ['Petit Gâteau', 'Bolo quente com sorvete de creme', '19.90', '6.50'],
        ],
    ];

    /**
     * Seed categories, products, and a test waiter for the given restaurant.
     *
     * @return array{categories: int, products: int, waiter_created: bool}
     */
    public function __invoke(Restaurant $restaurant): array
    {
        $categoriesCreated = 0;
        $productsCreated = 0;

        foreach (self::CATEGORIES as $name => $sortOrder) {
            $category = ProductCategory::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $name],
                ['sort_order' => $sortOrder],
            );

            if ($category->wasRecentlyCreated) {
                $categoriesCreated++;
            }

            $productsCreated += $this->seedProducts($restaurant, $category);
        }

        $waiter = Waiter::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'username' => 'garcom.teste'],
            [
                'name' => 'Garçom Teste',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        return [
            'categories' => $categoriesCreated,
            'products' => $productsCreated,
            'waiter_created' => $waiter->wasRecentlyCreated,
        ];
    }

    private function seedProducts(Restaurant $restaurant, ProductCategory $category): int
    {
        $created = 0;

        foreach (self::PRODUCTS[$category->name] ?? [] as $index => [$name, $description, $price, $cost]) {
            $product = Product::firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'product_category_id' => $category->id,
                    'name' => $name,
                ],
                [
                    'description' => $description,
                    'price' => $price,
                    'cost' => $cost,
                    'show_in_menu' => true,
                    'available' => true,
                    'sort_order' => $index + 1,
                ],
            );

            if ($product->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
