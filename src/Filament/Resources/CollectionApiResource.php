<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Filament\Clusters\Collections;
use A21ns1g4ts\FilamentCollections\Models\CollectionApi;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CollectionApiResource extends Resource
{
    protected static ?string $model = CollectionApi::class;

    protected static ?string $cluster = Collections::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(50),
                    Select::make('personal_access_token_id')
                        ->relationship('token', 'name')
                        ->label('Token Sanctum')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('configs')
                        ->relationship('configs', 'key')
                        ->multiple()
                        ->label('Collections')
                        ->preload()
                        ->searchable(),
                    Toggle::make('active')
                        ->default(true),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('token.name')
                    ->label('Token'),
                Tables\Columns\TextColumn::make('configs.key')
                    ->label('Collections')
                    ->badge(),
                Tables\Columns\IconColumn::make('active')->boolean(),
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
            'index' => Pages\ListCollectionApis::route('/'),
            'create' => Pages\CreateCollectionApi::route('/create'),
            'edit' => Pages\EditCollectionApi::route('/{record}/edit'),
        ];
    }
}
