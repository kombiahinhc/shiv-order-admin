<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderLinesRelationManager;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('salesperson.name')->label('Sales rep')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('shop.name')
                    ->label('Shop')
                    ->getStateUsing(fn (Order $record) => $record->shop?->name ?? $record->shop_name_snapshot)
                    ->searchable(),
                Tables\Columns\TextColumn::make('lines_count')->counts('lines')->label('Items'),
                Tables\Columns\TextColumn::make('subtotal')->money('INR'),
                Tables\Columns\TextColumn::make('tax_total')->money('INR')->label('Tax'),
                Tables\Columns\TextColumn::make('grand_total')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('sync_status')
                    ->badge()
                    ->color(fn (string $state): string => $state === Order::SYNC_SYNCED ? 'success' : 'warning'),
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('salesperson_id')
                    ->relationship('salesperson', 'name')
                    ->label('Sales rep'),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderLinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
