<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers;

use A21ns1g4ts\FilamentCollections\Models\CollectionData;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
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

    public function form(Schema $form): Schema
    {
        $schema = $this->ownerRecord->schema; // @phpstan-ignore-line

        return $form->schema([
            Section::make('Preenchimento dos Campos')
                ->description('Complete os dados da coleção conforme o schema configurado.')
                ->columnSpanFull()
                ->schema($this->getFieldsFromSchema($schema, $this->ownerRecord->id)),
        ]);
    }

    protected function getFieldsFromSchema(array $schema, ?int $configId = null, string $prefix = 'payload'): array
    {
        $sluggableFields = collect($schema)->filter(fn($f) => $f['sluggable'] ?? false);

        $fields = collect($schema)->map(function ($field) use ($configId, $prefix, $sluggableFields) {
            $name = $field['name'] ?? null;

            if (! $name || $name === 'uuid') {
                return null;
            }

            $label = $field['label'] ?? ucfirst($name);
            $type = $field['type'] ?? 'text';
            $required = $field['required'] ?? false;
            $default = $field['default'] ?? null;
            $hint = $field['hint'] ?? null;
            $unique = $field['unique'] ?? false;

            $fieldName = "{$prefix}.{$name}";

            $component = match ($type) {
                'text' => Forms\Components\TextInput::make($fieldName),
                'textarea' => Forms\Components\Textarea::make($fieldName),
                'select' => Forms\Components\Select::make($fieldName)
                    ->options(fn() => collect(explode("\n", $field['options'] ?? ''))
                        ->mapWithKeys(function ($line) {
                            $line = trim($line);

                            return str_contains($line, ':')
                                ? [explode(':', $line, 2)[0] => explode(':', $line, 2)[1]]
                                : [$line => $line];
                        })->toArray()),
                'boolean' => Forms\Components\Toggle::make($fieldName),
                'number' => Forms\Components\TextInput::make($fieldName)->numeric(),
                'date' => Forms\Components\DatePicker::make($fieldName),
                'datetime' => Forms\Components\DateTimePicker::make($fieldName),
                'color' => Forms\Components\ColorPicker::make($fieldName),
                'json' => JsonColumn::make($fieldName)
                    ->nullable()
                    ->editorOnly()
                    ->default(is_array($default) ? json_encode($default, JSON_PRETTY_PRINT) : $default),
                'collection' => Forms\Components\Select::make($fieldName)
                    ->options(function () use ($field) {
                        $targetCollectionKey = $field['target_collection_key'] ?? null;
                        if (! $targetCollectionKey) {
                            return [];
                        }
                        $targetCollectionConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
                        if (! $targetCollectionConfig) {
                            return [];
                        }
                        $targetCollectionTitle = $targetCollectionConfig->title_field ?? 'uuid';

                        return CollectionData::where('collection_config_id', $targetCollectionConfig->id)
                            ->get()
                            ->pluck('payload.' . $targetCollectionTitle, 'payload.uuid')
                            ->toArray();
                    })
                    ->multiple(fn() => ($field['relationship_type'] ?? 'belongsTo') === 'belongsToMany')
                    ->visible(fn() => in_array($field['relationship_type'] ?? 'belongsTo', ['belongsTo', 'belongsToMany']))
                    ->searchable()
                    ->createOptionForm(function (Schema $schema) use ($field) {
                        $targetCollectionKey = $field['target_collection_key'] ?? null;
                        if (! $targetCollectionKey) {
                            return $schema;
                        }

                        $targetCollectionConfig = CollectionConfig::where('key', $targetCollectionKey)->first();

                        if (! $targetCollectionConfig) {
                            return $schema;
                        }

                        return $schema->schema($this->getFieldsFromSchema($targetCollectionConfig->schema, $targetCollectionConfig->id));
                    })
                    ->createOptionUsing(function (array $data) use ($field) {
                        $targetCollectionKey = $field['target_collection_key'] ?? null;
                        $targetCollectionConfig = CollectionConfig::where('key', $targetCollectionKey)->first();

                        $record = CollectionData::create([
                            'collection_config_id' => $targetCollectionConfig->id,
                            'payload' => array_merge($data['payload'] ?? $data, ['uuid' => Str::uuid()->toString()]),
                        ]);

                        return $record->payload['uuid'];
                    }),
                default => Forms\Components\TextInput::make($fieldName),
            };

            $component = $component
                ->label($label)
                ->required($required)
                ->default($default)
                ->helperText($hint)
                ->columnSpanFull();

            // Lógica de Slug em JS (evita requisições ao servidor)
            $targets = $sluggableFields->where('slug_source', $name);
            if ($targets->isNotEmpty()) {
                $jsLogic = '';
                foreach ($targets as $target) {
                    $targetPath = "{$prefix}.{$target['name']}";
                    $jsLogic .= "\$set('{$targetPath}', (\$state ?? '').toLowerCase().normalize('NFD').replace(/[\\u0300-\\u036f]/g, '').replace(/[^\\w\\s-]/g, '').replace(/[\\s_-]+/g, '-').replace(/^-+|-+$/g, ''));";
                }
                
                $component = $component->afterStateUpdatedJs($jsLogic);
            }

            if ($unique && $configId) {
                $component = $component->unique(
                    table: CollectionData::class,
                    column: "payload->{$name}",
                    ignorable: fn($record) => $record instanceof \A21ns1g4ts\FilamentCollections\Models\CollectionData ? $record : null,
                    modifyRuleUsing: function (Unique $rule, $record, $component) use ($name, $configId) {
                        $inputValue = $component->getState();
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
            ->values();

        if ($prefix === 'payload') {
            $fields = $fields->prepend(
                Forms\Components\TextInput::make("{$prefix}.uuid")
                    ->default(Str::uuid()->toString())
                    ->disabled()
                    ->dehydrated()
                    ->label('UUID')
                    ->required()
                    ->columnSpanFull()
            );
        }

        return $fields->all();
    }

    public function table(Table $table): Table
    {
        $schema = $this->ownerRecord->schema;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payload.uuid')
                    ->label('UUID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

                        'collection' => Tables\Columns\TextColumn::make("payload.{$name}")
                            ->label($label)
                            ->formatStateUsing(function ($state) use ($field) {
                                if (empty($state)) {
                                    return '';
                                }

                                $targetCollectionKey = $field['target_collection_key'] ?? null;

                                if (! $targetCollectionKey) {
                                    return is_array($state) ? implode(', ', $state) : $state;
                                }

                                $targetCollectionConfig = CollectionConfig::where('key', $targetCollectionKey)->first();

                                if (! $targetCollectionConfig) {
                                    return is_array($state) ? implode(', ', $state) : $state;
                                }

                                $targetCollectionTitle = $targetCollectionConfig->title_field ?? 'uuid';

                                $query = CollectionData::where('collection_config_id', $targetCollectionConfig->id);

                                if (is_array($state)) {
                                    $items = $query->whereIn('payload->uuid', $state)->get();

                                    return $items->pluck('payload.' . $targetCollectionTitle)->implode(', ');
                                }

                                $item = $query->where('payload->uuid', $state)->first();

                                return $item?->payload[$targetCollectionTitle] ?? $state;
                            }),

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
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }
}
