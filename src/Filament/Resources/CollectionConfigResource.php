<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Filament\Clusters\Collections;
use A21ns1g4ts\FilamentCollections\Filament\Components\ToggleNullable;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages\CreateCollectionConfigs;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages\EditCollectionConfigs;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages\ListCollectionConfigs;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers\DataRelationManager;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource; // Importar Closure
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use ValentinMorice\FilamentJsonColumn\JsonColumn;

class CollectionConfigResource extends Resource
{
    protected static ?string $model = CollectionConfig::class;

    protected static ?string $cluster = Collections::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationParentItem(): ?string
    {
        // not work __('filament-collections::default.resources.group.plural')
        return 'Grupos';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-collections::default.navigationLabel');
    }

    public static function getModelLabel(): string
    {
        return __('filament-collections::default.modelLabel');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-collections::default.modelLabelPlural');
    }

    public static function form(Schema $form): Schema
    {
        return $form->columns(1)->schema([

            Section::make(__('filament-collections::default.sections.general'))
                ->columns(['default' => 3])
                ->columnSpanFull()
                ->schema([
                    Select::make('collection_group_id')
                        ->relationship('group', 'name')
                        ->label(__('filament-collections::default.fields.group'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(1),

                    TextInput::make('key')
                        ->label(__('filament-collections::default.fields.key'))
                        ->helperText(__('filament-collections::default.fields.key_help'))
                        ->required()
                        ->default('collection_')
                        ->maxLength(50)
                        ->regex('/^[a-z_]+$/')
                        ->unique(CollectionConfig::class, 'key', ignoreRecord: true)
                        ->disabled(fn ($operation) => $operation === 'edit')
                        ->columnSpan(1),

                    Select::make('title_field')
                        ->label(__('filament-collections::default.fields.title_field'))
                        ->options(function ($get) {
                            $schema = $get('schema') ?? [];

                            return collect($schema)
                                ->filter(fn ($field) => $field['name'])
                                ->where('type', '!==', 'collection')
                                ->pluck('name', 'name')
                                ->toArray();
                        })
                        ->required()
                        ->reactive()
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->label(__('filament-collections::default.fields.description'))
                        ->rows(2)
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Section::make(__('filament-collections::default.form.fields_section'))
                ->description(__('filament-collections::default.form.fields_description'))
                ->columnSpanFull()
                ->schema([
                    Repeater::make('schema')
                        ->label(__('filament-collections::default.fields.fields'))
                        ->addActionLabel(__('filament-collections::default.actions.add_field'))
                        ->itemLabel(fn ($state) => $state['name'] ?? __('filament-collections::default.labels.new_field'))
                        ->collapsible()
                        ->collapsed()
                        ->cloneable()
                        ->orderColumn()
                        ->schema([
                            Group::make()->columns(5)->schema([
                                Select::make('type')
                                    ->label(__('filament-collections::default.fields.type'))
                                    ->default('text')
                                    ->options([
                                        'text' => __('filament-collections::default.types.text'),
                                        'textarea' => __('filament-collections::default.types.textarea'),
                                        'select' => __('filament-collections::default.types.select'),
                                        'boolean' => __('filament-collections::default.types.boolean'),
                                        'number' => __('filament-collections::default.types.number'),
                                        'date' => __('filament-collections::default.types.date'),
                                        'datetime' => __('filament-collections::default.types.datetime'),
                                        'color' => __('filament-collections::default.types.color'),
                                        'json' => __('filament-collections::default.types.json'),
                                        'collection' => __('filament-collections::default.fields.modelLabel') ?? 'Collection',
                                    ])
                                    ->required()
                                    ->reactive(),

                                TextInput::make('name')
                                    ->label(__('filament-collections::default.fields.name'))
                                    ->required()
                                    ->maxLength(50)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->debounce(500)
                                    // Validação para nome único dentro do repeater
                                    ->rules([
                                        fn ($get, $state, $livewire) => function (string $attribute, $value, Closure $fail) use ($get, $livewire) {
                                            if ($value === 'uuid') {
                                                $fail("O nome 'uuid' é reservado pelo sistema.");
                                            }

                                            $currentRepeaterItems = $get('../../schema'); // Pega todos os itens do repeater
                                            $currentFieldUuid = $livewire->currentlyOpenRepeaterItems[$attribute] ?? null; // Obtém o UUID do item atual, se disponível

                                            $count = collect($currentRepeaterItems)
                                                ->filter(fn ($item, $uuid) => ($item['name'] ?? null) === $value && $uuid !== $currentFieldUuid)
                                                ->count();

                                            if ($count > 1) {
                                                $fail("O nome '{$value}' já está em uso em outro campo.");
                                            }
                                        },
                                    ]),

                                TextInput::make('label')
                                    ->label(__('filament-collections::default.fields.label'))
                                    ->maxLength(100)
                                    ->nullable()
                                    ->columnSpan(2)
                                    // Validação para label único dentro do repeater
                                    ->rules([
                                        fn ($get, $state, $livewire) => function (string $attribute, $value, Closure $fail) use ($get, $livewire) {
                                            if (empty($value)) { // Permite que labels vazias sejam repetidas
                                                return;
                                            }
                                            $currentRepeaterItems = $get('../../schema');
                                            $currentFieldUuid = $livewire->currentlyOpenRepeaterItems[$attribute] ?? null;

                                            $count = collect($currentRepeaterItems)
                                                ->filter(fn ($item, $uuid) => ($item['label'] ?? null) === $value && $uuid !== $currentFieldUuid)
                                                ->count();

                                            if ($count > 1) {
                                                $fail("O rótulo '{$value}' já está em uso em outro campo.");
                                            }
                                        },
                                    ]),
                            ]),

                            Group::make()->columns(5)->schema([
                                Textarea::make('options')
                                    ->label(__('filament-collections::default.fields.options'))
                                    ->helperText(__('filament-collections::default.fields.options_help'))
                                    ->rows(3)
                                    ->visible(fn ($get) => $get('type') === 'select')
                                    ->columnSpan(5),
                            ]),

                            Group::make()->columns(2)->schema([
                                Select::make('relationship_type')
                                    ->label(__('filament-collections::default.fields.relationship_type'))
                                    ->options([
                                        'belongsTo' => __('filament-collections::default.options.belongsTo'),
                                        'hasOne' => __('filament-collections::default.options.hasOne'),
                                        'hasMany' => __('filament-collections::default.options.hasMany'),
                                        'belongsToMany' => __('filament-collections::default.options.belongsToMany'),
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->visible(fn ($get) => $get('type') === 'collection'),

                                Select::make('target_collection_key')
                                    ->label(__('filament-collections::default.fields.target_collection'))
                                    ->options(
                                        CollectionConfig::all()->pluck('key', 'key')->toArray()
                                    )
                                    ->required()
                                    ->reactive()
                                    ->visible(fn ($get) => $get('type') === 'collection')
                                    ->createOptionForm(fn (Schema $schema) => static::form($schema))
                                    ->createOptionUsing(function (array $data) {
                                        return CollectionConfig::create($data)->key;
                                    }),

                                Select::make('foreign_key_on_target')
                                    ->label(__('filament-collections::default.fields.foreign_key_on_target'))
                                    ->helperText(__('filament-collections::default.fields.foreign_key_on_target_help'))
                                    ->options(function ($get) {
                                        $targetCollectionKey = $get('target_collection_key');
                                        if (! $targetCollectionKey) {
                                            return [];
                                        }

                                        $targetConfig = CollectionConfig::where('key', $targetCollectionKey)->first();
                                        if (! $targetConfig) {
                                            return [];
                                        }

                                        return collect($targetConfig->schema)
                                            ->pluck('name', 'name')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->visible(fn ($get) => in_array($get('relationship_type'), ['hasMany', 'hasOne'])),

                                Select::make('on_delete')
                                    ->label(__('filament-collections::default.fields.on_delete'))
                                    ->options([
                                        'restrict' => __('filament-collections::default.options.restrict'),
                                        'cascade' => __('filament-collections::default.options.cascade'),
                                        'set_null' => __('filament-collections::default.options.set_null'),
                                    ])
                                    ->default('restrict')
                                    ->required(),
                            ])
                                ->visible(fn ($get) => $get('type') === 'collection'),

                            Group::make()->columns(8)->schema([
                                Toggle::make('required')
                                    ->label(__('filament-collections::default.fields.required'))
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(1),

                                Toggle::make('unique')
                                    ->label(__('filament-collections::default.fields.unique'))
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpan(1),

                                Toggle::make('sluggable')
                                    ->label(__('filament-collections::default.fields.generate_slug'))
                                    ->reactive()
                                    ->inline(false)
                                    ->visible(fn ($get) => $get('type') === 'text')
                                    ->columnSpan(1),

                                Select::make('slug_source')
                                    ->label(__('filament-collections::default.fields.slug_source'))
                                    ->options(function ($get) {
                                        $schema = $get('../../schema') ?? [];

                                        return collect($schema)
                                            ->filter(fn ($field) => ($field['name'] ?? null) && ($field['name'] ?? null) !== ($get('name') ?? null))
                                            ->pluck('name', 'name')
                                            ->toArray();
                                    })
                                    ->required(fn ($get) => $get('sluggable'))
                                    ->visible(fn ($get) => $get('sluggable') && $get('type') === 'text')
                                    ->columnSpan(5),

                                TextInput::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->default(null)
                                    ->visible(fn ($get) => ! in_array($get('type'), ['select', 'json', 'number', 'boolean', 'datetime', 'date', 'color', 'collection']))
                                    ->columnSpan(4),

                                ColorPicker::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->visible(fn ($get) => $get('type') === 'color')
                                    ->columnSpan(4),

                                JsonColumn::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->editorOnly()
                                    ->visible(fn ($get) => $get('type') === 'json')
                                    ->columnSpan(4),

                                TextInput::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->numeric()
                                    ->visible(fn ($get) => $get('type') === 'number')
                                    ->columnSpan(4),

                                DateTimePicker::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->visible(fn ($get) => $get('type') === 'datetime')
                                    ->columnSpan(4),

                                DatePicker::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->visible(fn ($get) => $get('type') === 'date')
                                    ->columnSpan(4),

                                ToggleNullable::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->visible(fn ($get) => $get('type') === 'boolean')
                                    ->columnSpan(4),

                                Select::make('default')
                                    ->label(__('filament-collections::default.fields.default'))
                                    ->nullable()
                                    ->options(fn ($get) => collect(explode("\n", $get('options') ?? ''))
                                        ->mapWithKeys(function ($line) {
                                            $line = trim($line);

                                            return str_contains($line, ':')
                                                ? [explode(':', $line, 2)[0] => explode(':', $line, 2)[1]]
                                                : [$line => $line];
                                        })->toArray())
                                    ->visible(fn ($get) => $get('type') === 'select')
                                    ->columnSpan(4),

                                TextInput::make('hint')
                                    ->label(__('filament-collections::default.fields.hint'))
                                    ->nullable()
                                    ->maxLength(255)
                                    ->columnSpan(4),
                            ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('filament-collections::default.fields.key'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament-collections::default.fields.description'))
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-collections::default.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollectionConfigs::route('/'),
            'create' => CreateCollectionConfigs::route('/create'),
            'edit' => EditCollectionConfigs::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            DataRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [];
    }
}
