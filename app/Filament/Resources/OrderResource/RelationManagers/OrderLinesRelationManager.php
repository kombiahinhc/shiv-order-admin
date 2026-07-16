<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class OrderLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('product_name')->disabled(),
            Forms\Components\TextInput::make('qty')->numeric()->disabled(),
            Forms\Components\TextInput::make('unit_price')->numeric()->disabled(),
            Forms\Components\TextInput::make('discount')->numeric()->disabled(),
            Forms\Components\TextInput::make('tax_rate')->numeric()->disabled(),
            Forms\Components\TextInput::make('line_total')->numeric()->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('product_name'),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('qty')->numeric(),
                Tables\Columns\TextColumn::make('unit_price')->money('INR'),
                Tables\Columns\TextColumn::make('discount')->money('INR'),
                Tables\Columns\TextColumn::make('tax_rate')->suffix('%'),
                Tables\Columns\TextColumn::make('line_total')->money('INR'),
            ])
            ->headerActions([
                // read-only; lines come from the mobile app
            ])
            ->actions([
                Actions\ViewAction::make(),
            ]);
    }
}
