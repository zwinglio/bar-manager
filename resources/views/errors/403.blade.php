<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Acesso negado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center px-6 py-12">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-8 w-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 0h10.5a1.5 1.5 0 0 1 1.5 1.5v6.75a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-6.75a1.5 1.5 0 0 1 1.5-1.5Z" />
                </svg>
            </div>

            <p class="text-sm font-semibold uppercase tracking-wider text-red-600">Erro 403</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Provavelmente você entrou no sistema errado</h1>
            <p class="mt-3 text-sm text-slate-600">
                Você não tem acesso a esta área. Verifique o painel correto para o seu perfil.
            </p>

            @php
                $user = auth()->user();
                $target = $user?->hasRole('admin') ? url('/admin')
                        : ($user?->hasRole('restaurant') ? url('/restaurant') : url('/'));
                $label = $user?->hasRole('admin') ? 'Ir para o painel do Administrador'
                        : ($user?->hasRole('restaurant') ? 'Ir para o painel do Restaurante' : 'Voltar ao início');
            @endphp

            <a href="{{ $target }}"
               class="mt-6 inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                {{ $label }}
            </a>
        </div>
    </div>
</body>
</html>
