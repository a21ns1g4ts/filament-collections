#!/bin/sh

# Função para criar arquivo vazio se não existir
touch_if_not_exists() {
  [ ! -f "$1" ] && touch "$1"
}

mkdir -p config resources/css resources/dist resources/js resources/lang/en resources/lang/pt resources/views \
src/Commands src/Facades \
src/Filament/Resources/CollectionConfigResource/Pages src/Filament/Resources/CollectionConfigResource/Widgets \
src/Filament/Resources/CollectionDataResource/Pages src/Filament/Resources/CollectionDataResource/Widgets \
src/Testing tests/database/migrations workbench/app/Providers

touch_if_not_exists config/filament-collections.php
touch_if_not_exists CONTRIBUTING.md
touch_if_not_exists LICENSE.md
touch_if_not_exists package.json
touch_if_not_exists phpunit.xml.dist
touch_if_not_exists pint.json
touch_if_not_exists postcss.config.cjs
touch_if_not_exists README.md

touch_if_not_exists resources/css/index.css
touch_if_not_exists resources/js/index.js
touch_if_not_exists resources/lang/en/collections.php
touch_if_not_exists resources/lang/pt/collections.php

touch_if_not_exists src/Commands/FilamentCollectionsInstallCommand.php
touch_if_not_exists src/Facades/FilamentCollections.php
touch_if_not_exists src/FilamentCollections.php
touch_if_not_exists src/FilamentCollectionsPlugin.php
touch_if_not_exists src/FilamentCollectionsServiceProvider.php
touch_if_not_exists src/Testing/TestsFilamentCollections.php

touch_if_not_exists src/Filament/Resources/CollectionConfigResource.php
touch_if_not_exists src/Filament/Resources/CollectionDataResource.php

touch_if_not_exists tests/ArchTest.php
touch_if_not_exists tests/database/migrations/create_collections_config_table.php
touch_if_not_exists tests/database/migrations/create_collections_data_table.php
touch_if_not_exists tests/ExampleTest.php
touch_if_not_exists tests/Pest.php
touch_if_not_exists tests/TestCase.php

touch_if_not_exists workbench/app/Providers/WorkbenchServiceProvider.php

echo "Estrutura básica criada com sucesso!"
