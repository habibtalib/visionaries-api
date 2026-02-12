<?php
namespace App\Filament\Resources;

use App\Filament\Resources\TraitResource\Pages;
use App\Models\Trait_;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class TraitResource extends Resource
{
    protected static ?string $model = Trait_::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;
    protected static ?string $navigationLabel = 'Traits';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(50),
            Forms\Components\Textarea::make('description'),
            Forms\Components\Textarea::make('why_template'),
            Forms\Components\Textarea::make('daily_template'),
            Forms\Components\Textarea::make('opposite_template'),
            Forms\Components\TextInput::make('category')->maxLength(100),
            Forms\Components\Toggle::make('is_default')->default(true),
            Forms\Components\Toggle::make('is_custom')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTraits::route('/'),
            'create' => Pages\CreateTrait_::route('/create'),
            'edit' => Pages\EditTrait_::route('/{record}/edit'),
        ];
    }
}
