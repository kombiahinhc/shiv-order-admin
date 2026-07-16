<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('sku')->maxLength(255),
            Forms\Components\TextInput::make('category')->maxLength(255),
            Forms\Components\TextInput::make('unit')->maxLength(50),
            Forms\Components\TextInput::make('list_price')->numeric()->required()->default(0)->prefix('₹'),
            Forms\Components\TextInput::make('tax_rate')->numeric()->required()->default(0)->suffix('%'),
            Forms\Components\Toggle::make('active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('category')->searchable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('list_price')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('tax_rate')->suffix('%'),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
