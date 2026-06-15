<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $restaurant->name }} — Cardápio</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-stone-50 text-stone-800 antialiased">
        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-3xl px-4 py-6 text-center">
                <h1 class="text-2xl font-bold tracking-tight text-stone-900">{{ $restaurant->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">Cardápio</p>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-6">
            @forelse ($categories as $category)
                <section class="mb-8">
                    <h2 class="mb-4 text-lg font-semibold text-stone-900">{{ $category->name }}</h2>

                    <div class="space-y-4">
                        @foreach ($category->products as $product)
                            <div class="flex items-start gap-4 rounded-xl bg-white p-4 shadow-sm">
                                @if ($product->photo_path)
                                    <img
                                        src="{{ Storage::url($product->photo_path) }}"
                                        alt="{{ $product->name }}"
                                        class="h-20 w-20 flex-shrink-0 rounded-lg object-cover"
                                    >
                                @else
                                    <div class="h-20 w-20 flex-shrink-0 rounded-lg bg-stone-200"></div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="truncate text-base font-medium text-stone-900">
                                            {{ $product->name }}
                                        </h3>
                                        <span class="whitespace-nowrap text-base font-semibold text-amber-600">
                                            R$ {{ number_format($product->price, 2, ',', '.') }}
                                        </span>
                                    </div>

                                    @if ($product->description)
                                        <p class="mt-1 text-sm leading-relaxed text-stone-600">
                                            {{ $product->description }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <p class="text-center text-stone-500">Nenhum item disponível no cardápio no momento.</p>
            @endforelse
        </main>
    </body>
</html>
