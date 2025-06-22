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
