<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppVersionResource\Pages;
use App\Models\AppVersion;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string|UnitEnum|null $navigationGroup = 'App';
    protected static ?string $navigationLabel = 'App Versions';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Version Info')->schema([
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('1.0.0'),
                Forms\Components\TextInput::make('build_number')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Toggle::make('is_force_update')
                    ->label('Force Update')
                    ->default(false),
            ]),
            Section::make('Release Notes')->schema([
                Forms\Components\Textarea::make('release_notes')
                    ->rows(4)
                    ->placeholder('What\'s new in this version...'),
            ]),
            Section::make('APK File')->schema([
                Forms\Components\FileUpload::make('apk_path')
                    ->label('APK File')
                    ->disk('public_storage')
                    ->acceptedFileTypes([
                        'application/vnd.android.package-archive',
                        'application/octet-stream',
                        'application/zip',
                        'application/x-zip-compressed',
                    ])
                    ->maxSize(102400) // 100MB
                    ->directory('apk')
                    ->required()
                    ->downloadable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')->sortable(),
                Tables\Columns\TextColumn::make('build_number')->sortable(),
                Tables\Columns\IconColumn::make('is_force_update')->label('Force'),
                Tables\Columns\TextColumn::make('release_notes')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListAppVersions::route('/'),
            'create' => Pages\CreateAppVersion::route('/create'),
            'edit' => Pages\EditAppVersion::route('/{record}/edit'),
        ];
    }
}
