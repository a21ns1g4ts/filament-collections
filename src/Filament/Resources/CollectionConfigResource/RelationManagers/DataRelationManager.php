<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers;

use Filament\Resources\RelationManagers\HasManyRelationManager;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DataRelationManager extends RelationManager
{
    protected static ?string $pluralModelLabel = 'Dados da Coleção';

    protected static ?string $title = 'Dados da Coleção';

    protected static ?string $modelLabel = 'Dado da Coleção';

    protected static string $relationship = 'data';

    protected static ?string $recordTitleAttribute = 'id'; // ou outro campo, se houver

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Aqui você monta o form para os dados da coleção.
                // Como o payload é JSON e dinâmico, você pode mostrar um Textarea simples ou
                // usar alguma UI customizada para edição do JSON.
                Forms\Components\Textarea::make('payload')
                    ->label('Dados da Coleção')
                    ->rows(10)
                    ->json() // validação JSON
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payload')
                    ->label('Dados')
                    ->limit(100)
                    ->wrap(),
            ])
            ->filters([
                // filtros se quiser
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
