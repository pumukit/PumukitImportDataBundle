Import Data Bundle
==================

This bundle requires PuMuKIT version 3 or higher. To import data on lower versions use [PumukitExampleDataBundle](https://github.com/pumukit/PumukitExampleDataBundle)


```bash
composer require teltek/pumukit-import-data-bundle
```

### Install the Bundle

If you have [PumukitInstallBundle](https://github.com/pumukit/PumukitInstallBundle) execute te following command

```bash
php app/console pumukit:install:bundle Pumukit/ImportDataBundle/PumukitImportDataBundle
```

if not, add this to app/AppKernel.php

```
new Pumukit\ImportDataBundle\PumukitImportDataBundle()
```

Then execute the following commands

```bash
php app/console cache:clear
php app/console cache:clear --env=prod
php app/console assets:install
```
