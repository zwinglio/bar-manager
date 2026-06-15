<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sistema de Gerenciamento de Bares e Restaurantes</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-zinc-950 text-zinc-100 antialiased">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-zinc-950/80 backdrop-blur-md border-b border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="#" class="text-xl font-black tracking-tight text-amber-400">BarManager</a>

                    <div class="hidden md:flex items-center gap-8">
                        <a href="#sobre" class="text-sm font-medium text-zinc-300 hover:text-amber-400 transition-colors">Sobre</a>
                        <a href="#funcionalidades" class="text-sm font-medium text-zinc-300 hover:text-amber-400 transition-colors">Funcionalidades</a>
                        <a href="#acessar" class="text-sm font-medium text-zinc-300 hover:text-amber-400 transition-colors">Acessar</a>
                    </div>

                    <button id="mobile-menu-btn" class="md:hidden p-2 text-zinc-300 hover:text-amber-400" aria-label="Menu">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-zinc-800">
                    <a href="#sobre" class="block py-3 text-sm font-medium text-zinc-300 hover:text-amber-400 transition-colors">Sobre</a>
                    <a href="#funcionalidades" class="block py-3 text-sm font-medium text-zinc-300 hover:text-amber-400 transition-colors">Funcionalidades</a>
                    <a href="#acessar" class="block py-3 text-sm font-medium text-zinc-300 hover:text-amber-400 transition-colors">Acessar</a>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 via-rose-500/10 to-zinc-950"></div>
            <div class="relative max-w-5xl mx-auto text-center">
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white mb-6">
                    Gerencie seu bar <span class="text-amber-400">sem perder a piada.</span>
                </h1>
                <p class="text-lg sm:text-xl text-zinc-300 max-w-2xl mx-auto mb-10">
                    O sistema completo para donos de bares e restaurantes que querem controle total — mesas, pedidos, cardápios e garçons — tudo na palma da mão.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4" id="acessar">
                    <a href="/restaurant" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-zinc-950 bg-amber-400 rounded-xl hover:bg-amber-300 transition-colors">
                        Administrar Restaurante
                    </a>
                    <button id="open-waiter-modal" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-rose-500 rounded-xl hover:bg-rose-400 transition-colors">
                        Acessar APP do Garçom
                    </button>
                </div>
            </div>
        </section>

        <!-- Sobre -->
        <section id="sobre" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl font-black tracking-tight text-white mb-6">Sobre</h2>
                <p class="text-lg text-zinc-300">
                    Esse é um sistema privado de gerenciamento de restaurantes. Desenvolvido para simplificar o dia a dia de bares, lanchonetes e restaurantes, reunindo em uma única plataforma tudo o que o dono e o garçom precisam para trabalhar com agilidade.
                </p>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section id="funcionalidades" class="py-20 px-4 sm:px-6 lg:px-8 bg-zinc-900/50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black tracking-tight text-white text-center mb-14">Funcionalidades</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Restaurante -->
                    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 hover:border-amber-400/50 transition-colors">
                        <div class="w-12 h-12 bg-amber-400/10 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Restaurante</h3>
                        <p class="text-zinc-400 leading-relaxed">
                            Gestão completa de mesas, produtos, categorias e garçons. Controle estoque, preços e visualize tudo em tempo real.
                        </p>
                    </div>

                    <!-- Garçom -->
                    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 hover:border-rose-500/50 transition-colors">
                        <div class="w-12 h-12 bg-rose-500/10 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Garçom</h3>
                        <p class="text-zinc-400 leading-relaxed">
                            Interface mobile otimizada para abrir mesas, lançar pedidos rapidamente e fechar contas com praticidade.
                        </p>
                    </div>

                    <!-- Cardápio -->
                    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 hover:border-emerald-400/50 transition-colors">
                        <div class="w-12 h-12 bg-emerald-400/10 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Cardápio</h3>
                        <p class="text-zinc-400 leading-relaxed">
                            Menu público acessível por link ou QR Code para cada restaurante. Seus clientes acessam o cardápio direto do celular.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white mb-8">
                    Pronto para simplificar seu restaurante?
                </h2>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/restaurant" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-zinc-950 bg-amber-400 rounded-xl hover:bg-amber-300 transition-colors">
                        Administrar Restaurante
                    </a>
                    <button id="open-waiter-modal-2" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white bg-rose-500 rounded-xl hover:bg-rose-400 transition-colors">
                        Acessar APP do Garçom
                    </button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-800 py-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-zinc-400">
                    Desenvolvido por <a href="https://zwinglio.com" target="_blank" class="text-amber-400 hover:underline">Zwinglio</a> · Feito no Brasil · &copy; 2026
                </p>
                <p class="text-sm text-zinc-500">BarManager — Sistema de Gerenciamento de Bares e Restaurantes</p>
            </div>
        </footer>

        <!-- Waiter Modal -->
        <div id="waiter-modal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
            <div id="waiter-modal-backdrop" class="absolute inset-0 bg-zinc-950/80 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="relative bg-zinc-900 border border-zinc-800 rounded-2xl p-6 w-full max-w-md shadow-2xl">
                    <h3 class="text-lg font-bold text-white mb-4">Acessar APP do Garçom</h3>
                    <p class="text-sm text-zinc-400 mb-4">Digite o slug do restaurante para acessar o painel do garçom.</p>
                    <input
                        id="waiter-slug"
                        type="text"
                        placeholder="ex: meu-restaurante"
                        class="w-full px-4 py-3 bg-zinc-950 border border-zinc-700 rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-400 mb-4"
                    >
                    <div class="flex gap-3">
                        <button id="close-waiter-modal" class="flex-1 px-4 py-3 text-sm font-medium text-zinc-300 bg-zinc-800 rounded-xl hover:bg-zinc-700 transition-colors">
                            Cancelar
                        </button>
                        <button id="go-waiter" class="flex-1 inline-flex items-center justify-center px-4 py-3 text-sm font-bold text-zinc-950 bg-amber-400 rounded-xl hover:bg-amber-300 transition-colors opacity-50 pointer-events-none">
                            Acessar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const mobileMenuBtn = document.getElementById('mobile-menu-btn');
                const mobileMenu = document.getElementById('mobile-menu');
                const menuIcon = document.getElementById('menu-icon');
                const closeIcon = document.getElementById('close-icon');

                if (mobileMenuBtn) {
                    mobileMenuBtn.addEventListener('click', () => {
                        mobileMenu.classList.toggle('hidden');
                        menuIcon.classList.toggle('hidden');
                        closeIcon.classList.toggle('hidden');
                    });
                }

                const modal = document.getElementById('waiter-modal');
                const backdrop = document.getElementById('waiter-modal-backdrop');
                const slugInput = document.getElementById('waiter-slug');
                const goBtn = document.getElementById('go-waiter');

                function openModal() {
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    slugInput.focus();
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                }

                function updateGoButton() {
                    const hasValue = slugInput.value.trim().length > 0;
                    if (hasValue) {
                        goBtn.classList.remove('opacity-50', 'pointer-events-none');
                    } else {
                        goBtn.classList.add('opacity-50', 'pointer-events-none');
                    }
                }

                document.getElementById('open-waiter-modal').addEventListener('click', openModal);
                document.getElementById('open-waiter-modal-2').addEventListener('click', openModal);
                document.getElementById('close-waiter-modal').addEventListener('click', closeModal);
                backdrop.addEventListener('click', closeModal);

                slugInput.addEventListener('input', updateGoButton);
                slugInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && slugInput.value.trim()) {
                        window.location.href = '/waiter/' + slugInput.value.trim() + '/login';
                    }
                });

                goBtn.addEventListener('click', () => {
                    const slug = slugInput.value.trim();
                    if (slug) {
                        window.location.href = '/waiter/' + slug + '/login';
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>
