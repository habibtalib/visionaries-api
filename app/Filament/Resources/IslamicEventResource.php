<?php
namespace App\Filament\Resources;

use App\Filament\Resources\IslamicEventResource\Pages;
use App\Models\IslamicEvent;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class IslamicEventResource extends Resource
{
    protected static ?string $model = IslamicEvent::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(200),
            Forms\Components\TextInput::make('title_ms')->label('Title (MS)')->maxLength(200),
            Forms\Components\Textarea::make('description'),
            Forms\Components\DatePicker::make('event_date')->required(),
            Forms\Components\TextInput::make('hijri_date')->maxLength(50),
            Forms\Components\TextInput::make('type')->maxLength(50),
            Forms\Components\Toggle::make('is_recurring'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('event_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('hijri_date'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('is_recurring')->boolean(),
            ])
            ->defaultSort('event_date', 'asc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIslamicEvents::route('/'),
            'create' => Pages\CreateIslamicEvent::route('/create'),
            'edit' => Pages\EditIslamicEvent::route('/{record}/edit'),
        ];
    }
}
