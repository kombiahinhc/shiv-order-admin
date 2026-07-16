<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order')
                    ->schema([
                        TextEntry::make('order_date')->date(),
                        TextEntry::make('salesperson.name')->label('Sales rep'),
                        TextEntry::make('shop.name')
                            ->label('Shop')
                            ->getStateUsing(fn ($record) => $record->shop?->name ?? $record->shop_name_snapshot),
                        TextEntry::make('notes')->columnSpanFull(),
                    ])
                    ->columns(3),
                Section::make('Totals')
                    ->schema([
                        TextEntry::make('subtotal')->money('INR'),
                        TextEntry::make('tax_total')->money('INR')->label('Tax'),
                        TextEntry::make('grand_total')->money('INR'),
                        TextEntry::make('discount_type')->label('Discount'),
                        TextEntry::make('discount_value'),
                    ])
                    ->columns(5),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
