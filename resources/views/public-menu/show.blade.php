<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $restaurant->name }} — Cardápio</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-stone-50 text-stone-800 antialiased">
        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-3xl px-4 py-6 text-center">
                @if ($restaurant->logo_path)
                    <img
                        src="{{ Storage::url($restaurant->logo_path) }}"
                        alt="{{ $restaurant->name }}"
                        class="mx-auto mb-4 h-20 w-20 rounded-full object-cover"
                    >
                @endif
                <h1 class="text-2xl font-bold tracking-tight text-stone-900">{{ $restaurant->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">Cardápio</p>
            </div>
        </header>

        @if ($categories->isNotEmpty())
            <nav class="sticky top-0 z-10 border-b border-stone-200 bg-white/95 backdrop-blur">
                <div class="mx-auto max-w-3xl overflow-x-auto px-4">
                    <ul class="flex gap-2 py-3">
                        @foreach ($categories as $category)
                            <li class="flex-shrink-0">
                                <a
                                    href="#categoria-{{ $category->id }}"
                                    data-category="categoria-{{ $category->id }}"
                                    class="inline-block rounded-full bg-stone-100 px-4 py-1.5 text-sm font-medium text-stone-600 transition-colors"
                                >{{ $category->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>
        @endif

        <main class="mx-auto max-w-3xl px-4 py-6">
            @forelse ($categories as $category)
                <section id="categoria-{{ $category->id }}" class="mb-8 scroll-mt-28">
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

        @if ($restaurant->address)
            <footer class="mt-10 border-t border-stone-200 py-6 text-center text-sm text-stone-500">
                {{ $restaurant->address }}
            </footer>
        @endif

        <button
            id="back-to-top"
            type="button"
            class="fixed bottom-4 right-4 z-20 rounded-full bg-amber-600 p-3 text-white shadow-lg opacity-0 pointer-events-none transition-opacity duration-300"
            aria-label="Voltar ao topo"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
            </svg>
        </button>

        <script>
            (function () {
                const tabs = document.querySelectorAll('a[data-category]');
                const sections = document.querySelectorAll('section[id^="categoria-"]');

                if (!tabs.length || !sections.length) return;

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            tabs.forEach(tab => {
                                if (tab.dataset.category === entry.target.id) {
                                    tab.classList.add('bg-amber-600', 'text-white');
                                    tab.classList.remove('bg-stone-100', 'text-stone-600');
                                } else {
                                    tab.classList.remove('bg-amber-600', 'text-white');
                                    tab.classList.add('bg-stone-100', 'text-stone-600');
                                }
                            });
                        }
                    });
                }, {
                    rootMargin: '-20% 0px -60% 0px',
                    threshold: 0,
                });

                sections.forEach(section => observer.observe(section));

                const backToTop = document.getElementById('back-to-top');
                if (backToTop) {
                    const toggleVisibility = () => {
                        if (window.scrollY > 300) {
                            backToTop.classList.remove('opacity-0', 'pointer-events-none');
                        } else {
                            backToTop.classList.add('opacity-0', 'pointer-events-none');
                        }
                    };
                    window.addEventListener('scroll', toggleVisibility, { passive: true });
                    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
                }
            })();
        </script>
    </body>
</html>
