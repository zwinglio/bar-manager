<?php

namespace App\Filament\Restaurant\Pages;

use App\Actions\Development\SeedRestaurantMockData;
use App\Models\Restaurant;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DevTools extends Page
{
    protected string $view = 'filament.restaurant.pages.dev-tools';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Ferramentas de Desenvolvimento';

    protected static ?string $title = 'Ferramentas de Desenvolvimento';

    protected static ?int $navigationSort = 100;

    public static function shouldRegisterNavigation(): bool
    {
        return app()->environment(['local', 'staging']);
    }

    public static function canAccess(): bool
    {
        return app()->environment(['local', 'staging']);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('seedMockData')
                ->label('Preencher com dados de teste')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Preencher com dados de teste?')
                ->modalDescription('Isso criará categorias, produtos e um garçom de teste para o seu restaurante. Registros existentes não serão duplicados.')
                ->action(function (SeedRestaurantMockData $seeder): void {
                    $restaurant = Auth::user()?->restaurant;
                    abort_unless($restaurant instanceof Restaurant, 403);

                    $result = $seeder($restaurant);

                    Notification::make()
                        ->success()
                        ->title('Dados de teste criados')
                        ->body("Categorias: {$result['categories']} · Produtos: {$result['products']} · Garçom: garcom.teste / password")
                        ->send();
                }),
        ];
    }
}
