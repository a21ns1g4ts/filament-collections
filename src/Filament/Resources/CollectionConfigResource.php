<?php

namespace A21ns1g4ts\FilamentCollections\Filament\Resources;

use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages\CreateCollectionConfigs;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages\EditCollectionConfigs;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\Pages\ListCollectionConfigs;
use A21ns1g4ts\FilamentCollections\Filament\Resources\CollectionConfigResource\RelationManagers\DataRelationManager;
use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CollectionConfigResource extends Resource
{
    protected static ?string $model = CollectionConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-collections::default.form.identification'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label(__('filament-collections::default.fields.key'))
                            ->helperText(__('filament-collections::default.fields.key_help'))
                            ->required()
                            ->maxLength(50)
                            ->regex('/^[a-z_]+$/')
                            ->unique(CollectionConfig::class, 'key', ignoreRecord: true)
                            ->columnSpan(2)
                            ->disabled(fn ($operation) => $operation === 'edit'),

                        Textarea::make('description')
                            ->label(__('filament-collections::default.fields.description'))
                            ->rows(2)
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpan(2),
                    ]),

                Section::make(__('filament-collections::default.form.fields_section'))
                    ->description(__('filament-collections::default.form.fields_description'))
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
                                Group::make()
                                    ->schema([
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
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->columnSpan(1),

                                        TextInput::make('name')
                                            ->label(__('filament-collections::default.fields.name'))
                                            ->required()
                                            ->maxLength(50)
                                            ->columnSpan(2),

                                        TextInput::make('label')
                                            ->label(__('filament-collections::default.fields.label'))
                                            ->maxLength(100)
                                            ->nullable()
                                            ->columnSpan(2),
                                    ])
                                    ->columns(5),

                                Group::make()
                                    ->schema([
                                        Textarea::make('options')
                                            ->label(__('filament-collections::default.fields.options'))
                                            ->helperText(__('filament-collections::default.fields.options_help'))
                                            ->rows(3)
                                            ->visible(fn ($get) => $get('type') === 'select')
                                            ->columnSpan(5),
                                    ])
                                    ->columns(5),

                                Group::make()
                                    ->schema([
                                        Toggle::make('required')
                                            ->label(__('filament-collections::default.fields.required'))
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1),

                                        TextInput::make('default')
                                            ->label(__('filament-collections::default.fields.default'))
                                            ->default(null)
                                            ->visible(fn ($get) => ! in_array($get('type'), ['select', 'boolean', 'datetime', 'date']))
                                            ->nullable()
                                            ->columnSpan(2),

                                        DateTimePicker::make('default')
                                            ->label(__('filament-collections::default.fields.default'))
                                            ->default(null)
                                            ->visible(fn ($get) => $get('type') === 'datetime')
                                            ->nullable()
                                            ->columnSpan(2),

                                        DatePicker::make('default')
                                            ->label(__('filament-collections::default.fields.default'))
                                            ->default(null)
                                            ->visible(fn ($get) => $get('type') === 'date')
                                            ->nullable()
                                            ->columnSpan(2),

                                        Toggle::make('default')
                                            ->label(__('filament-collections::default.fields.default'))
                                            ->default(null)
                                            ->inline(false)
                                            ->nullable()
                                            ->dehydrated(false)
                                            ->visible(fn ($get) => $get('type') === 'boolean')
                                            ->columnSpan(2),

                                        Select::make('default')
                                            ->default(null)
                                            ->label(__('filament-collections::default.fields.default'))
                                            ->options(
                                                fn ($get) => collect(explode("\n", $get('options') ?? ''))
                                                    ->mapWithKeys(function ($line) {
                                                        $line = trim($line);
                                                        return str_contains($line, ':')
                                                            ? [explode(':', $line, 2)[0] => explode(':', $line, 2)[1]]
                                                            : [$line => $line];
                                                    })->toArray()
                                            )
                                            ->visible(fn ($get) => $get('type') === 'select')
                                            ->columnSpan(2),

                                        TextInput::make('hint')
                                            ->label(__('filament-collections::default.fields.hint'))
                                            ->maxLength(255)
                                            ->nullable()
                                            ->columnSpan(4),
                                    ])
                                    ->columns(7),
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
