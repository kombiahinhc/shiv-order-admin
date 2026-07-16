<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Models\Shop;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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
            Section::make('Basic Info')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('owner')->maxLength(255),
                    Forms\Components\TextInput::make('phone')->maxLength(50),
                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                    Forms\Components\TextInput::make('gst_number')
                        ->label('GST Number')
                        ->maxLength(20)
                        ->columnSpanFull(),
                ]),
            Section::make('Location')
                ->schema([
                    Forms\Components\TextInput::make('latitude')
                        ->numeric()
                        ->minValue(-90)
                        ->maxValue(90)
                        ->step(0.0000001),
                    Forms\Components\TextInput::make('longitude')
                        ->numeric()
                        ->minValue(-180)
                        ->maxValue(180)
                        ->step(0.0000001),
                ])->columns(2),
            Section::make('Shop Image')
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Shop Image')
                        ->image()
                        ->directory('shops')
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ]),
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
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/no-image.png')),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('owner')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('gst_number')->label('GST'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === Shop::STATUS_APPROVED ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('requestedBy.name')->label('Requested by'),
                Tables\Columns\TextColumn::make('latitude'),
                Tables\Columns\TextColumn::make('longitude'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Shop::STATUS_PENDING => 'Pending',
                        Shop::STATUS_APPROVED => 'Approved',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Basic Info')
                ->schema([
                    Infolists\Components\TextEntry::make('name'),
                    Infolists\Components\TextEntry::make('owner'),
                    Infolists\Components\TextEntry::make('phone'),
                    Infolists\Components\TextEntry::make('address'),
                    Infolists\Components\TextEntry::make('gst_number')->label('GST Number'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => $state === Shop::STATUS_APPROVED ? 'success' : 'warning'),
                ])->columns(2),
            Section::make('Location')
                ->schema([
                    Infolists\Components\TextEntry::make('latitude'),
                    Infolists\Components\TextEntry::make('longitude'),
                ])->columns(2),
            Section::make('Shop Image')
                ->schema([
                    Infolists\Components\ImageEntry::make('image_path')
                        ->disk('public')
                        ->defaultImageUrl(url('/images/no-image.png')),
                ]),
            Infolists\Components\TextEntry::make('requestedBy.name')
                ->label('Requested by'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'view' => Pages\ViewShop::route('/{record}'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
