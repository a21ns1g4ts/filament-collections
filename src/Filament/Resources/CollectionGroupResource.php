<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Filament\Clusters\Collections;
use A21ns1g4ts\FilamentCollections\Models\CollectionGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section as SchemaSection;

class CollectionGroupResource extends Resource
{
    protected static ?string $model = CollectionGroup::class;

    protected static ?string $cluster = Collections::class;

    public static function getModelLabel(): string
    {
        return __('filament-collections::default.resources.group.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-collections::default.resources.group.plural');
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            SchemaSection::make()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('key')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('configs_count')
                    ->counts('configs')
                    ->label('Collections'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionGroups::route('/'),
            'create' => Pages\CreateCollectionGroup::route('/create'),
            'edit' => Pages\EditCollectionGroup::route('/{record}/edit'),
        ];
    }
}
