<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_menu_returns_success_for_active_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create(['is_active' => true]);
        $category = ProductCategory::factory()->create([
            'restaurant_id' => $restaurant->id,
            'sort_order' => 1,
        ]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'show_in_menu' => true,
            'available' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(200);
        $response->assertSee($restaurant->name);
        $response->assertSee($category->name);
    }

    public function test_public_menu_returns_404_for_inactive_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create(['is_active' => false]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(404);
    }

    public function test_hides_products_not_showing_in_menu(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        $visibleProduct = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'name' => 'Produto Visível',
            'show_in_menu' => true,
            'available' => true,
        ]);
        $hiddenProduct = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'name' => 'Produto Oculto',
            'show_in_menu' => false,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertSee('Produto Visível');
        $response->assertDontSee('Produto Oculto');
    }

    public function test_hides_unavailable_products(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        $availableProduct = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'name' => 'Produto Disponível',
            'show_in_menu' => true,
            'available' => true,
        ]);
        $unavailableProduct = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'name' => 'Produto Indisponível',
            'show_in_menu' => true,
            'available' => false,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertSee('Produto Disponível');
        $response->assertDontSee('Produto Indisponível');
    }

    public function test_categories_are_ordered_by_sort_order_then_name(): void
    {
        $restaurant = Restaurant::factory()->create();
        $categoryB = ProductCategory::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Bebidas',
            'sort_order' => 2,
        ]);
        $categoryA = ProductCategory::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Porções',
            'sort_order' => 1,
        ]);

        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $categoryA->id,
            'show_in_menu' => true,
            'available' => true,
        ]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $categoryB->id,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertSeeInOrder(['Porções', 'Bebidas']);
    }

    public function test_products_are_ordered_by_sort_order_then_name(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);

        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'name' => 'Coca-Cola',
            'sort_order' => 2,
            'show_in_menu' => true,
            'available' => true,
        ]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'name' => 'Água',
            'sort_order' => 1,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->id])); // Using id to avoid route model binding confusion with slug
        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertSeeInOrder(['Água', 'Coca-Cola']);
    }

    public function test_empty_categories_are_hidden(): void
    {
        $restaurant = Restaurant::factory()->create();
        $emptyCategory = ProductCategory::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Categoria Vazia',
        ]);
        $categoryWithProduct = ProductCategory::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Categoria com Produto',
        ]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $categoryWithProduct->id,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertDontSee('Categoria Vazia');
        $response->assertSee('Categoria com Produto');
    }

    public function test_shows_message_when_no_products_available(): void
    {
        $restaurant = Restaurant::factory()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'show_in_menu' => false,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertSee('Nenhum item disponível no cardápio no momento.');
    }

    public function test_shows_logo_when_present(): void
    {
        $restaurant = Restaurant::factory()->create([
            'logo_path' => 'logos/test-logo.png',
        ]);
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(200);
        $response->assertSee('logos/test-logo.png');
    }

    public function test_hides_logo_when_not_present(): void
    {
        $restaurant = Restaurant::factory()->create(['logo_path' => null]);
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(200);
        $response->assertDontSee('rounded-full');
    }

    public function test_shows_address_in_footer_when_present(): void
    {
        $restaurant = Restaurant::factory()->create([
            'address' => 'Rua das Flores, 123',
        ]);
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(200);
        $response->assertSee('Rua das Flores, 123');
        $response->assertSee('<footer', false);
    }

    public function test_hides_address_footer_when_not_present(): void
    {
        $restaurant = Restaurant::factory()->create(['address' => null]);
        $category = ProductCategory::factory()->create(['restaurant_id' => $restaurant->id]);
        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'product_category_id' => $category->id,
            'show_in_menu' => true,
            'available' => true,
        ]);

        $response = $this->get(route('menu.public', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(200);
        $response->assertDontSee('<footer', false);
    }
}
