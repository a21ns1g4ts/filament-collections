<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Filament\Clusters\Collections;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionGroupResource\Pages;
use A21ns1g4ts\FilamentCollections\Models\CollectionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

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
        return $form->columns(1)->schema([
            SchemaSection::make()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('filament-collections::default.fields.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('key')
                        ->label(__('filament-collections::default.fields.key'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    Textarea::make('description')
                        ->label(__('filament-collections::default.fields.description'))
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-collections::default.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('filament-collections::default.fields.key'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('configs_count')
                    ->counts('configs')
                    ->label(__('filament-collections::default.fields.collections')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-collections::default.fields.created_at'))
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
