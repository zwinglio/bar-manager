<x-filament-panels::page>
    <ul wire:sort="updateCategoryOrder" class="space-y-4">
        @foreach ($categories as $category)
            <li wire:sort:item="{{ $category->id }}" wire:key="cat-{{ $category->id }}">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-950 dark:text-white">
                        <span class="cursor-move text-gray-400 select-none">☰</span>
                        {{ $category->name }}
                    </h3>

                    <ul
                        wire:sort="updateProductOrder"
                        wire:sort:group="products"
                        wire:sort:group-id="{{ $category->id }}"
                        class="space-y-2"
                    >
                        @foreach ($category->products as $product)
                            <li
                                wire:sort:item="{{ $product->id }}"
                                wire:key="prod-{{ $product->id }}"
                                class="flex items-center gap-2 rounded-lg border border-gray-200 p-3 dark:border-gray-700 cursor-move"
                            >
                                <span class="text-gray-400 select-none">☰</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</span>
                                <span class="ml-auto text-sm text-gray-500 dark:text-gray-400">
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
        @endforeach
    </ul>
</x-filament-panels::page>
