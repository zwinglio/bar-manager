<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Pages;

use App\Enums\PaymentMethod;
use App\Filament\Restaurant\Resources\RestaurantTables\RestaurantTableResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditRestaurantTable extends EditRecord
{
    protected static string $resource = RestaurantTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close')
                ->label('Fechar mesa')
                ->color('danger')
                ->icon(Heroicon::OutlinedLockClosed)
                ->visible(fn (): bool => $this->getRecord()->isOpen())
                ->requiresConfirmation()
                ->schema([
                    Select::make('payment_method')
                        ->label('Forma de pagamento')
                        ->options(collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $m) => [$m->value => $m->label()]))
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $this->getRecord()->close(PaymentMethod::from($data['payment_method']));

                    Notification::make()
                        ->success()
                        ->title('Mesa fechada com sucesso.')
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
