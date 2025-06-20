<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DataRelationManager extends RelationManager
{
    protected static ?string $pluralModelLabel = 'Itens da Coleção';

    protected static ?string $title = 'Itens';

    protected static ?string $modelLabel = 'Item da Coleção';

    protected static string $relationship = 'data';

    protected static ?string $recordTitleAttribute = 'id'; // ou outro campo, se houver

    public function form(Form $form): Form
    {
        $schema = $this->ownerRecord->schema;

        return $form
            ->schema([
                Section::make('Preenchimento dos Campos')
                    ->description('Complete os dados da coleção conforme o schema configurado.')
                    ->schema(
                        collect($schema)->map(function ($field) {
                            $name = $field['name'] ?? null;

                            if (! $name) {
                                return null;
                            }

                            $label = $field['label'] ?? ucfirst($name);
                            $type = $field['type'] ?? 'text';
                            $required = $field['required'] ?? false;
                            $default = $field['default'] ?? null;
                            $hint = $field['hint'] ?? null;

                            return match ($type) {
                                'text' => Forms\Components\TextInput::make("payload.{$name}")
                                    ->label($label)->required($required)->default($default)->helperText($hint),

                                'textarea' => Forms\Components\Textarea::make("payload.{$name}")
                                    ->label($label)->required($required)->default($default)->helperText($hint),

                                'select' => Forms\Components\Select::make("payload.{$name}")
                                    ->label($label)
                                    // ->options(json_decode($field['value']) ?? [])
                                    ->options(
                                        fn ($get) => collect(explode("\n", $field['options'] ?? ''))
                                            ->mapWithKeys(function ($line) {
                                                $line = trim($line);

                                                return str_contains($line, ':')
                                                    ? [explode(':', $line, 2)[0] => explode(':', $line, 2)[1]]
                                                    : [$line => $line];
                                            })->toArray()
                                    )
                                    ->required($required)
                                    ->default($default)
                                    ->helperText($hint),

                                'boolean' => Forms\Components\Toggle::make("payload.{$name}")
                                    ->label($label)->required($required)->default((bool) $default)->helperText($hint),

                                'number' => Forms\Components\TextInput::make("payload.{$name}")
                                    ->label($label)->numeric()->required($required)->default($default)->helperText($hint),

                                'date' => Forms\Components\DatePicker::make("payload.{$name}")
                                    ->label($label)->required($required)->default($default)->helperText($hint),

                                'datetime' => Forms\Components\DateTimePicker::make("payload.{$name}")
                                    ->label($label)->required($required)->default($default)->helperText($hint),

                                'color' => Forms\Components\ColorPicker::make("payload.{$name}")
                                    ->label($label)->required($required)->default($default)->helperText($hint),

                                'json' => Forms\Components\Textarea::make("payload.{$name}")
                                    ->label($label)
                                    ->required($required)
                                    ->rows(6)
                                    ->default(is_array($default) ? json_encode($default, JSON_PRETTY_PRINT) : $default)
                                    ->helperText($hint),

                                default => Forms\Components\TextInput::make("payload.{$name}")
                                    ->label($label)->required($required)->default($default)->helperText($hint),
                            };
                        })->filter()->values()->all()
                    ),
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
