<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ActionResource\Pages;
use App\Models\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;
    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('user.display_name')->label('User'),
                Tables\Columns\TextColumn::make('domain')->badge(),
                Tables\Columns\TextColumn::make('frequency')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([Tables\Filters\TrashedFilter::make()]);
    }

    public static function canCreate(): bool { return false; }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActions::route('/')];
    }
}
