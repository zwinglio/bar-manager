<x-filament-panels::page>
    @php
        $restaurant = $this->getRestaurant();
        $url = $this->getPublicMenuUrl();
    @endphp

    <div class="mx-auto max-w-xl space-y-6">
        {{-- Header --}}
        <div class="text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-100 dark:bg-white/5">
                <x-heroicon-o-book-open class="h-7 w-7 text-gray-600 dark:text-gray-300" />
            </div>
            <h2 class="text-xl font-semibold text-gray-950 dark:text-white">
                {{ $restaurant?->name ?? 'Seu Restaurante' }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Cardápio público — compartilhe com seus clientes
            </p>
        </div>

        {{-- Link do cardápio --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-500/10">
                    <x-heroicon-o-link class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Link do cardápio</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Compartilhe com seus clientes</p>
                </div>
            </div>

            <div class="mt-4">
                <label class="sr-only" for="menu-url">URL do cardápio</label>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input
                            id="menu-url"
                            type="text"
                            readonly
                            value="{{ $url }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 pr-20 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        />
                        <button
                            type="button"
                            onclick="copyMenuUrl()"
                            class="absolute right-1 top-1/2 -translate-y-1/2 rounded-md px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700"
                        >
                            <span id="copy-label">Copiar</span>
                        </button>
                    </div>
                    <x-filament::button
                        tag="a"
                        :href="$url"
                        target="_blank"
                        icon="heroicon-o-arrow-top-right-on-square"
                        size="sm"
                    >
                        Abrir
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- QR Code --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-heroicon-o-qr-code class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">QR Code</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Escaneie para acessar o cardápio</p>
                </div>
            </div>

            <div class="mt-6 flex justify-center">
                <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 dark:bg-white">
                    {!! $this->getQrCodeSvg() !!}
                </div>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400 dark:text-gray-500">
                Aponte a câmera do celular para escanear
            </p>
        </div>

        {{-- Como usar --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Como usar</h3>

            <div class="mt-4 space-y-4">
                <div class="flex gap-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700 dark:bg-white/5 dark:text-gray-300">1</div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Imprima o QR Code</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Baixe a imagem e imprima para colocar nas mesas.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700 dark:bg-white/5 dark:text-gray-300">2</div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Compartilhe o link</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Envie por WhatsApp, redes sociais ou e-mail.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-700 dark:bg-white/5 dark:text-gray-300">3</div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Atualize quando quiser</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Alterações de produtos e preços refletem automaticamente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyMenuUrl() {
            const input = document.getElementById('menu-url');
            if (!input) return;

            input.select();
            input.setSelectionRange(0, 99999);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }

            const label = document.getElementById('copy-label');
            if (label) {
                label.textContent = 'Copiado!';
                setTimeout(() => {
                    label.textContent = 'Copiar';
                }, 2000);
            }
        }
    </script>
</x-filament-panels::page>
