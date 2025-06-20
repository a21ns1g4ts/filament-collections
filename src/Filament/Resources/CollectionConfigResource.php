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

    protected static ?string $navigationLabel = 'Coleções';

    protected static ?string $modelLabel = 'Coleção';

    protected static ?string $modelLabelPlural = 'Coleções';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identificação da Coleção')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label('Chave da Coleção')
                            ->helperText('Identificador único em snake_case (ex: blog_posts)')
                            ->required()
                            ->maxLength(50)
                            ->regex('/^[a-z_]+$/')
                            ->unique(CollectionConfig::class, 'key', ignoreRecord: true)
                            ->columnSpan(2)
                            ->disabled(fn ($operation) => $operation === 'edit'),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(2)
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpan(2),
                    ]),

                Section::make('Campos da Coleção')
                    ->description('Configure os campos que farão parte da sua coleção.')
                    ->schema([
                        Repeater::make('schema')
                            ->label('Campos')
                            ->addActionLabel('Adicionar Campo')
                            ->itemLabel(fn ($state) => $state['name'] ?? 'Novo Campo')
                            ->collapsible()
                            ->collapsed()
                            ->cloneable()
                            ->orderColumn()
                            // ->afterStateHydrated(
                            //     fn($component, $state, $record) => $component->state($record?->schema ?? [])
                            // )
                            // ->afterStateUpdated(
                            //     fn ($state, callable $set) => $set('schema', json_encode(array_values($state ?? [])))
                            // )
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Select::make('type')
                                            ->label('Tipo')
                                            ->default('text')
                                            ->options([
                                                'text' => 'Texto',
                                                'textarea' => 'Área de Texto',
                                                'select' => 'Seleção',
                                                'boolean' => 'Booleano',
                                                'number' => 'Número',
                                                'date' => 'Data',
                                                'datetime' => 'Data e Hora',
                                                'color' => 'Cor',
                                                'json' => 'JSON',
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->columnSpan(1),

                                        TextInput::make('name')
                                            ->label('Nome')
                                            ->required()
                                            ->maxLength(50)
                                            ->columnSpan(2),

                                        TextInput::make('label')
                                            ->label('Rótulo')
                                            ->maxLength(100)
                                            ->nullable()
                                            ->columnSpan(2),
                                    ])
                                    ->columns(5),

                                Group::make()
                                    ->schema([
                                        Textarea::make('options')
                                            ->label('Opções (select)')
                                            ->helperText('valor:Label por linha')
                                            ->rows(3)
                                            ->visible(fn ($get) => $get('type') === 'select')
                                            ->columnSpan(5),
                                    ])
                                    ->columns(5),

                                Group::make()
                                    ->schema([
                                        Toggle::make('required')
                                            ->label('Obrigatório?')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1),

                                        TextInput::make('default')
                                            ->label('Valor Padrãos')
                                            ->default(null)
                                            ->visible(fn ($get) => ! in_array($get('type'), ['select', 'boolean', 'datetime', 'date']))
                                            ->nullable()
                                            ->columnSpan(2),

                                        DateTimePicker::make('default')
                                            ->label('Valor Padrão')
                                            ->default(null)
                                            ->visible(fn ($get) => $get('type') === 'datetime')
                                            ->nullable()
                                            ->columnSpan(2),

                                        DatePicker::make('default')
                                            ->label('Valor Padrão')
                                            ->default(null)
                                            ->visible(fn ($get) => $get('type') === 'date')
                                            ->nullable()
                                            ->columnSpan(2),

                                        Toggle::make('default')
                                            ->label('Valor Padrão')
                                            ->default(null)
                                            ->inline(false)
                                            ->nullable()
                                            ->dehydrated(false)
                                            ->visible(fn ($get) => $get('type') === 'boolean')
                                            ->columnSpan(2),

                                        Select::make('default')
                                            ->default(null)
                                            ->label('Valor Padrão')
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
                                            ->label('Ajuda')
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
                    ->label(__('collections.fields.key'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('collections.fields.description'))
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('collections.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Add filters if needed
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
