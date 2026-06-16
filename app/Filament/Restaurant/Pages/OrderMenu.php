<?php

namespace App\Filament\Restaurant\Pages;

use App\Filament\Restaurant\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class OrderMenu extends Page
{
    protected string $view = 'filament.restaurant.pages.order-menu';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?string $navigationLabel = 'Ordenar Cardápio';

    protected static ?string $title = 'Ordenar Cardápio';

    protected static string|\UnitEnum|null $navigationGroup = 'Cardápio';

    /** @var Collection<int, ProductCategory> */
    public Collection $categories;

    public function mount(): void
    {
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $restaurantId = Auth::user()?->restaurant_id;

        $this->categories = ProductCategory::where('restaurant_id', $restaurantId)
            ->with(['products' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function updateCategoryOrder(int $id, int $position): void
    {
        $restaurantId = Auth::user()?->restaurant_id;

        $category = ProductCategory::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        $ordered = ProductCategory::where('restaurant_id', $restaurantId)
            ->orderBy('sort_order')
            ->get()
            ->reject(fn (ProductCategory $cat) => $cat->id === $category->id)
            ->values();

        $ordered->splice($position, 0, [$category]);

        foreach ($ordered as $index => $cat) {
            $cat->update(['sort_order' => $index]);
        }

        $this->loadCategories();
    }

    public function updateProductOrder(int $id, int $position, ?int $groupId = null): void
    {
        $restaurantId = Auth::user()?->restaurant_id;

        $product = Product::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        if ($groupId !== null && $product->product_category_id !== $groupId) {
            return;
        }

        $ordered = Product::where('restaurant_id', $restaurantId)
            ->where('product_category_id', $product->product_category_id)
            ->orderBy('sort_order')
            ->get()
            ->reject(fn (Product $p) => $p->id === $product->id)
            ->values();

        $ordered->splice($position, 0, [$product]);

        foreach ($ordered as $index => $p) {
            $p->update(['sort_order' => $index]);
        }

        $this->loadCategories();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Voltar')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(ProductResource::getUrl('index')),
        ];
    }
}
