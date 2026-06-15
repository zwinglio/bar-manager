<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Restaurant;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMenu extends Page
{
    protected string $view = 'filament.restaurant.pages.public-menu';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Cardápio Público';

    protected static ?string $title = 'Cardápio Público';

    protected static string|\UnitEnum|null $navigationGroup = 'Cardápio';

    public function getRestaurant(): ?Restaurant
    {
        return Auth::user()?->restaurant;
    }

    public function getPublicMenuUrl(): string
    {
        $restaurant = $this->getRestaurant();

        return $restaurant
            ? route('menu.public', ['restaurant' => $restaurant->slug])
            : '';
    }

    public function getQrCodeSvg(): string
    {
        $url = $this->getPublicMenuUrl();

        return $url
            ? QrCode::size(300)->generate($url)
            : '';
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadQr')
                ->label('Baixar QR Code')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): StreamedResponse {
                    $url = $this->getPublicMenuUrl();
                    $restaurant = $this->getRestaurant();
                    $fileName = 'qr-cardapio-'.($restaurant?->slug ?? 'restaurante').'.png';

                    $png = QrCode::format('png')
                        ->size(512)
                        ->margin(2)
                        ->generate($url);

                    return response()->streamDownload(function () use ($png): void {
                        echo $png;
                    }, $fileName, ['Content-Type' => 'image/png']);
                }),
        ];
    }
}
