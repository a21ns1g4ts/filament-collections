<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers;

use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
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

        return $form->schema([
            Section::make('Preenchimento dos Campos')
                ->description('Complete os dados da coleção conforme o schema configurado.')
                ->schema([
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

                        $component = match ($type) {
                            'text' => Forms\Components\TextInput::make("payload.{$name}"),
                            'textarea' => Forms\Components\Textarea::make("payload.{$name}"),
                            'select' => Forms\Components\Select::make("payload.{$name}")
                                ->options(fn () => collect(explode("\n", $field['options'] ?? ''))
                                    ->mapWithKeys(function ($line) {
                                        $line = trim($line);

                                        return str_contains($line, ':')
                                            ? [explode(':', $line, 2)[0] => explode(':', $line, 2)[1]]
                                            : [$line => $line];
                                    })->toArray()),
                            'boolean' => Forms\Components\Toggle::make("payload.{$name}"),
                            'number' => Forms\Components\TextInput::make("payload.{$name}")->numeric(),
                            'date' => Forms\Components\DatePicker::make("payload.{$name}"),
                            'datetime' => Forms\Components\DateTimePicker::make("payload.{$name}"),
                            'color' => Forms\Components\ColorPicker::make("payload.{$name}"),
                            'json' => JsonColumn::make("payload.{$name}")
                                ->nullable()
                                ->editorOnly()
                                ->default(is_array($default) ? json_encode($default, JSON_PRETTY_PRINT) : $default),
                            'collection' => Forms\Components\Select::make("payload.{$name}")
                                ->options(function () use ($field) {
                                    $targetCollectionKey = $field['target_collection_key'] ?? null;
                                    if (!$targetCollectionKey) {
                                        return [];
                                    }
                                    $targetCollectionConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
                                    if (!$targetCollectionConfig) {
                                        return [];
                                    }
                                    return CollectionData::where('collection_config_id', $targetCollectionConfig->id)
                                        ->get()
                                        ->pluck('payload.uuid', 'payload.uuid')
                                        ->toArray();
                                })
                                ->multiple(fn() => ($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany')
                                ->searchable(),
                            default => Forms\Components\TextInput::make("payload.{$name}"),
                        };

                        $component = $component
                            ->label($label)
                            ->required($required)
                            ->default($default)
                            ->helperText($hint);

                        if ($unique) {
                            $component = $component->unique(
                                table: CollectionData::class,
                                column: "payload->{$name}",
                                ignorable: fn ($record) => $record instanceof \A21ns1g4ts\FilamentCollections\Models\CollectionData ? $record : null,
                                modifyRuleUsing: function (Unique $rule, $record, $component) use ($name) {
                                    $inputValue = $component->getState();
                                    $configId = $this->ownerRecord->id;
                                    $uuid = $record?->payload['uuid'] ?? null;

                                    $rule = $rule->where('collection_config_id', $configId)
                                        ->where("payload->{$name}", $inputValue);

                                    if ($uuid) {
                                        $rule = $rule->where('payload->uuid', '!=', $uuid);
                                    }

                                    return $rule;
                                }
                            );
                        }

                        return $component;
                    })
                        ->filter()
                        ->values()
                        ->all(),
                ]),
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
