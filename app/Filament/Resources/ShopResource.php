<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Models\Shop;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('owner')->maxLength(255),
            Forms\Components\TextInput::make('phone')->maxLength(50),
            Forms\Components\Textarea::make('address')->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options([
                    Shop::STATUS_PENDING => 'Pending',
                    Shop::STATUS_APPROVED => 'Approved',
                ])
                ->default(Shop::STATUS_PENDING),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('owner')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === Shop::STATUS_APPROVED ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('requestedBy.name')->label('Requested by'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Shop::STATUS_PENDING => 'Pending',
                        Shop::STATUS_APPROVED => 'Approved',
                    ]),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Shop $record): bool => $record->status === Shop::STATUS_PENDING)
                    ->action(function (Shop $record) {
                        $record->approve(auth()->user());
                        Notification::make()->success()->title('Shop approved')->send();
                    }),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('approveSelected')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn (Shop $shop) => $shop->approve(auth()->user()));
                            Notification::make()->success()->title('Shops approved')->send();
                        }),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
