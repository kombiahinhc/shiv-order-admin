<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required(),
            Forms\Components\TextInput::make('phone'),
            Forms\Components\Select::make('role')
                ->options([
                    User::ROLE_ADMIN => 'Admin',
                    User::ROLE_MANAGER => 'Manager',
                    User::ROLE_SALESREP => 'Sales Rep',
                ])
                ->default(User::ROLE_SALESREP)
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ($state !== User::ROLE_SALESREP) {
                        $set('manager_id', null);
                    }
                }),
            Forms\Components\Select::make('manager_id')
                ->label('Manager')
                ->options(fn () => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_MANAGER])->pluck('name', 'id'))
                ->nullable()
                ->visible(fn (Get $get) => $get('role') === User::ROLE_SALESREP)
                ->searchable(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('manager.name')->label('Manager')->placeholder('—'),
            ])
            ->filters([])
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
