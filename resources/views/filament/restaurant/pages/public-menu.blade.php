<x-filament-panels::page>
    <div class="mx-auto max-w-xl space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                Link do cardápio
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Compartilhe este link com seus clientes ou imprima o QR code abaixo para colar nas mesas.
            </p>

            <div class="mt-4 flex items-center gap-2">
                <input
                    type="text"
                    readonly
                    value="{{ $this->getPublicMenuUrl() }}"
                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                >
                <x-filament::button
                    tag="a"
                    :href="$this->getPublicMenuUrl()"
                    target="_blank"
                    icon="heroicon-o-arrow-top-right-on-square"
                    color="gray"
                >
                    Abrir
                </x-filament::button>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                QR Code
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Escaneie com a câmera do celular para acessar o cardápio.
            </p>

            <div class="mt-6 flex justify-center">
                {!! $this->getQrCodeSvg() !!}
            </div>
        </div>
    </div>
</x-filament-panels::page>
