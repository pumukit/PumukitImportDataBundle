Import Data Bundle
==================

This bundle requires PuMuKIT version 3 or higher. To import data on lower versions use [PumukitExampleDataBundle](https://github.com/pumukit/PumukitExampleDataBundle)

```bash
composer require teltek/pumukit-import-data-bundle
```

if not, add this to config/bundles.php

```
Pumukit\ImportDataBundle\PumukitImportDataBundle::class => ['all' => true]
```

Then execute the following commands

```bash
php bin/console cache:clear
php bin/console cache:clear --env=prod
php bin/console assets:install
```
