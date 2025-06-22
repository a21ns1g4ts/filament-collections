<?php

return [
    'navigationLabel' => 'Collections',
    'modelLabel' => 'Collection',
    'modelLabelPlural' => 'Collections',

    'form' => [
        'identification' => 'Collection Identification',
        'fields_section' => 'Collection Fields',
        'fields_description' => 'Configure the fields that will be part of your collection.',
    ],

    'actions' => [
        'add_field' => 'Add Field',
    ],

    'labels' => [
        'new_field' => 'New Field',
    ],

    'fields' => [
        'key' => 'Collection Key',
        'key_help' => 'Unique identifier in snake_case (e.g., blog_posts)',
        'description' => 'Description',
        'type' => 'Type',
        'name' => 'Name',
        'label' => 'Label',
        'options' => 'Options (select)',
        'options_help' => 'value:Label per line',
        'required' => 'Required?',
        'default' => 'Default Value',
        'hint' => 'Help Text',
        'fields' => 'Fields',
        'created_at' => 'Created At',
    ],

    'types' => [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'select' => 'Select',
        'boolean' => 'Boolean',
        'number' => 'Number',
        'date' => 'Date',
        'datetime' => 'Date & Time',
        'color' => 'Color',
        'json' => 'JSON',
    ],
];
