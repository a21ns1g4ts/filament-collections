<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CollectionDataResource extends Resource
{
    protected static ?string $model = CollectionData::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-database';

    protected static ?string $navigationLabel = 'Dados da Coleção';

    public static function form(Form $form): Form
    {
        // Buscando configurações para popular select de collections
        $configs = CollectionConfig::all()->pluck('key', 'id');

        return $form
            ->schema([
                Forms\Components\Select::make('collection_config_id')
                    ->label('Configuração da Coleção')
                    ->options($configs)
                    ->required()
                    ->reactive(),

                Forms\Components\Textarea::make('payload')
                    ->label('Payload JSON')
                    ->rows(15)
                    ->code()
                    ->required()
                    ->visible(fn ($get) => $get('collection_config_id') !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('collectionConfig.key')->label('Collection'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->label('Criado em'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }
}
