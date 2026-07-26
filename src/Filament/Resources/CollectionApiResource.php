<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Filament\Clusters\Collections;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionApiResource\Pages;
use A21ns1g4ts\FilamentCollections\Models\CollectionApi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CollectionApiResource extends Resource
{
    protected static ?string $model = CollectionApi::class;

    protected static ?string $cluster = Collections::class;

    public static function getModelLabel(): string
    {
        return __('filament-collections::default.resources.api.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-collections::default.resources.api.plural');
    }

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    public static function form(Schema $form): Schema
    {
        return $form->columns(1)->schema([
            Section::make()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('filament-collections::default.fields.name'))
                        ->required()
                        ->maxLength(50),
                    Select::make('personal_access_token_id')
                        ->relationship('token', 'name')
                        ->label(__('filament-collections::default.fields.token'))
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('configs')
                        ->relationship('configs', 'key')
                        ->multiple()
                        ->label(__('filament-collections::default.fields.collections'))
                        ->preload()
                        ->searchable(),
                    Select::make('groups')
                        ->relationship('groups', 'name')
                        ->multiple()
                        ->label(__('filament-collections::default.fields.groups'))
                        ->preload()
                        ->searchable(),
                    Toggle::make('active')
                        ->label(__('filament-collections::default.fields.active'))
                        ->default(true),
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
                Tables\Columns\TextColumn::make('token.name')
                    ->label(__('filament-collections::default.fields.token')),
                Tables\Columns\TextColumn::make('configs.key')
                    ->label(__('filament-collections::default.fields.collections'))
                    ->badge(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament-collections::default.fields.active'))
                    ->boolean(),
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
            'index' => Pages\ListCollectionApis::route('/'),
            'create' => Pages\CreateCollectionApi::route('/create'),
            'edit' => Pages\EditCollectionApi::route('/{record}/edit'),
        ];
    }
}
