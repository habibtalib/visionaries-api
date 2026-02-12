<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ReelResource\Pages;
use App\Models\Reel;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ReelResource extends Resource
{
    protected static ?string $model = Reel::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Textarea::make('content')->required(),
            Forms\Components\Textarea::make('content_ms')->label('Content (MS)'),
            Forms\Components\TextInput::make('author')->maxLength(100),
            Forms\Components\TextInput::make('category')->maxLength(50),
            Forms\Components\TextInput::make('gradient')->maxLength(100),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content')->limit(60)->searchable(),
                Tables\Columns\TextColumn::make('author'),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReels::route('/'),
            'create' => Pages\CreateReel::route('/create'),
            'edit' => Pages\EditReel::route('/{record}/edit'),
        ];
    }
}
