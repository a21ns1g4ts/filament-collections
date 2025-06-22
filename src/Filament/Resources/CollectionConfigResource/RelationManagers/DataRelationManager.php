<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers;

use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use ValentinMorice\FilamentJsonColumn\JsonColumn;

class DataRelationManager extends RelationManager
{
    protected static ?string $pluralModelLabel = 'Itens da Coleção';

    protected static ?string $title = 'Itens';

    protected static ?string $modelLabel = 'Item da Coleção';

    protected static string $relationship = 'data';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        $schema = $this->ownerRecord->schema; // @phpstan-ignore-line

        return $form
            ->schema([
                Section::make('Preenchimento dos Campos')
                    ->description('Complete os dados da coleção conforme o schema configurado.')
                    ->schema(
                        [
                            Forms\Components\TextInput::make('payload.uuid')
                                ->default(Str::uuid()->toString())
                                ->disabled()
                                ->dehydrated()
                                ->label('UUID')
                                ->required(),
                            ...collect($schema)->map(function ($field) {
                                $name = $field['name'] ?? null;

                                if (! $name) {
                                    return null;
                                }

                                $label = $field['label'] ?? ucfirst($name);
                                $type = $field['type'] ?? 'text';
                                $required = $field['required'] ?? false;
                                $default = $field['default'] ?? null;
                                $hint = $field['hint'] ?? null;
                                $unique = $field['unique'] ?? false;

                                return match ($type) {
                                    'text' => Forms\Components\TextInput::make("payload.{$name}")
                                        ->when(
                                            $unique,
                                            fn (Forms\Components\TextInput $component) => $component->unique(
                                                table: CollectionData::class,
                                                column: "payload->{$name}",
                                                // ignorable: fn ($record) => $record instanceof \A21ns1g4ts\FilamentCollections\Models\CollectionData ? $record : null,
                                                modifyRuleUsing: function (Unique $rule, $record, $component) use ($name) {
                                                    $inputValue = $component->getState();
                                                    $uuid = $record?->payload['uuid'];
                                                    $configId = $this->ownerRecord->id;

                                                    if (! $uuid) {
                                                        return $rule->where("payload->{$name}", $inputValue)
                                                            ->where('collection_config_id', $configId);
                                                    }

                                                    return $rule
                                                        ->where("payload->{$name}", $inputValue)
                                                        ->where('collection_config_id', $configId)
                                                        ->where('payload->uuid', '!=', $uuid);
                                                },
                                            )
                                        )
                                        ->label($label)
                                        ->required($required)
                                        ->default($default)
                                        ->helperText($hint),

                                    'textarea' => Forms\Components\Textarea::make("payload.{$name}")
                                        ->label($label)->required($required)->default($default)->helperText($hint),

                                    'select' => Forms\Components\Select::make("payload.{$name}")
                                        ->label($label)
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

                                    'json' => JsonColumn::make("payload.{$name}")
                                        ->nullable()
                                        ->editorOnly()
                                        ->label($label)
                                        ->required($required)
                                        ->default(is_array($default) ? json_encode($default, JSON_PRETTY_PRINT) : $default)
                                        ->helperText($hint),

                                    default => Forms\Components\TextInput::make("payload.{$name}")
                                        ->label($label)->required($required)->default($default)->helperText($hint),
                                };
                            })
                                ->filter()
                                ->unshift(

                                )
                                ->values()
                                ->all(),
                        ]
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        $schema = $this->ownerRecord->schema;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                ...collect($schema)->map(function ($field) {
                    $name = $field['name'] ?? null;

                    if (! $name) {
                        return null;
                    }

                    $label = $field['label'] ?? ucfirst($name);
                    $type = $field['type'] ?? 'text';

                    return match ($type) {
                        'boolean' => Tables\Columns\IconColumn::make("payload.{$name}")
                            ->label($label)
                            ->boolean(),

                        'date' => Tables\Columns\TextColumn::make("payload.{$name}")
                            ->label($label)
                            ->date(),

                        'datetime' => Tables\Columns\TextColumn::make("payload.{$name}")
                            ->label($label)
                            ->dateTime(),

                        default => Tables\Columns\TextColumn::make("payload.{$name}")
                            ->label($label)
                            ->wrap()
                            ->limit(50),
                    };
                })->filter()->values()->all(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            ])
            ->defaultSort('id', 'desc');
    }
}
