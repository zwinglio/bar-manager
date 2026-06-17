<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Restaurant;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RestaurantSettings extends Page
{
    protected string $view = 'filament.restaurant.pages.restaurant-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $title = 'Configurações do Restaurante';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getRecord())
            ->components([
                Section::make('Informações do Restaurante')
                    ->schema([
                        ImageEntry::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->hidden(fn (?Restaurant $record): bool => $record?->logo_path === null)
                            ->columnSpanFull(),
                        TextEntry::make('name')
                            ->label('Nome'),
                        TextEntry::make('slug')
                            ->label('Slug'),
                        TextEntry::make('address')
                            ->label('Endereço')
                            ->placeholder('—'),
                        TextEntry::make('phone')
                            ->label('Telefone')
                            ->placeholder('—'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Editar')
                ->icon('heroicon-o-pencil-square')
                ->fillForm(fn (): array => $this->getRecord()?->attributesToArray() ?? [])
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('address')
                        ->label('Endereço')
                        ->default(null),
                    TextInput::make('phone')
                        ->label('Telefone')
                        ->default(null),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->default(null),
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->disk('public')
                        ->directory('logos')
                        ->visibility('public')
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $record = $this->getRecord();
                    abort_unless($record !== null, 403);

                    $record->fill($data)->save();

                    Notification::make()
                        ->success()
                        ->title('Configurações salvas')
                        ->send();
                }),
        ];
    }

    public function getRecord(): ?Restaurant
    {
        return Auth::user()?->restaurant;
    }
}
