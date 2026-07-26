<?php

return [
    'navigationLabel' => 'Coleções',
    'modelLabel' => 'Coleção',
    'modelLabelPlural' => 'Coleções',

    'form' => [
        'identification' => 'Identificação da Coleção',
        'fields_section' => 'Campos da Coleção',
        'fields_description' => 'Configure os campos que farão parte da sua coleção.',
    ],

    'actions' => [
        'add_field' => 'Adicionar Campo',
    ],

    'labels' => [
        'new_field' => 'Novo Campo',
    ],

    'fields' => [
        'key' => 'Chave da Coleção',
        'key_help' => 'Identificador único em snake_case (ex: blog_posts)',
        'description' => 'Descrição',
        'type' => 'Tipo',
        'name' => 'Nome',
        'label' => 'Rótulo',
        'options' => 'Opções (select)',
        'options_help' => 'valor:Label por linha',
        'required' => 'Obrigatório?',
        'unique' => 'Único?',
        'default' => 'Valor Padrão',
        'hint' => 'Ajuda',
        'fields' => 'Campos',
        'created_at' => 'Criado em',
        'group' => 'Grupo',
        'groups' => 'Grupos',
        'title_field' => 'Campo de Título',
        'relationship_type' => 'Tipo de Relacionamento',
        'target_collection' => 'Coleção de Destino',
        'foreign_key_on_target' => 'Chave Estrangeira no Destino',
        'foreign_key_on_target_help' => 'O nome do campo na coleção de destino que se refere a este registro da coleção. Deixe vazio para gerar automaticamente como {collection}_uuid.',
        'on_delete' => 'Comportamento ao Deletar',
        'generate_slug' => 'Gera Slug',
        'slug_source' => 'Campo Origem',
        'token' => 'Token Sanctum',
        'collections' => 'Coleções',
        'active' => 'Ativo',
    ],

    'options' => [
        'belongsTo' => 'Pertence A (Belongs To)',
        'hasOne' => 'Tem Um (Has One)',
        'hasMany' => 'Tem Muitos (Has Many)',
        'belongsToMany' => 'Pertence a Muitos (Belongs To Many)',
        'restrict' => 'Restringir (Bloqueia se houver vinculados)',
        'cascade' => 'Cascata (Deleta vinculados)',
        'set_null' => 'Anular (Remove apenas a referência)',
    ],

    'sections' => [
        'general' => 'Geral',
    ],

    'resources' => [
        'group' => [
            'label' => 'Grupo',
            'plural' => 'Grupos',
        ],
        'config' => [
            'label' => 'Configuração',
            'plural' => 'Configurações',
        ],
        'api' => [
            'label' => "Api's",
            'plural' => "Api's",
        ],
    ],

    'types' => [
        'text' => 'Texto',
        'textarea' => 'Área de Texto',
        'select' => 'Seleção',
        'boolean' => 'Booleano',
        'number' => 'Número',
        'date' => 'Data',
        'datetime' => 'Data e Hora',
        'color' => 'Cor',
        'json' => 'JSON',
    ],
];
