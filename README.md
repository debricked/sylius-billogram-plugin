<h1 align="center">
    <a href="https://billogram.com/" target="_blank">
        <img src="https://billogram.com/static/images/billogram-og_@2X.png" />
    </a>
    <br />
    <a href="https://packagist.org/packages/debricked/billogram-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/debricked/billogram-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/debricked/billogram-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/debricked/billogram-plugin.svg" />
    </a>
    <a href="http://travis-ci.org/debricked/SyliusBillogramPlugin" title="Build status" target="_blank">
        <img src="https://img.shields.io/travis/debricked/SyliusBillogramPlugin/master.svg" />
    </a>
    <a href="https://packagist.org/packages/debricked/billogram-plugin" title="Total Downloads" target="_blank">
        <img src="https://poser.pugx.org/debricked/billogram-plugin/downloads" />
    </a>
</h1>

## Overview

This plugin allows you to integrate Billogram payment with Sylius platform app. It includes all Sylius and Billogram payment features, including recurring payment and refunding orders.

The plugin was developed with inspiration from BitBagCommerce's [SyliusMolliePlugin](https://github.com/BitBagCommerce/SyliusMolliePlugin).

## Installation
```bash
$ composer require debricked/billogram-plugin 
```
    
Add plugin dependencies to your AppKernel.php file:

```php
public function registerBundles()
{
    return array_merge(parent::registerBundles(), [
        ...
        
        new \Debricked\SyliusBillogramPlugin\DebrickedSyliusBillogramPlugin(),
    ]);
}
```

Import required config in your `app/config/config.yml` file:

```yaml
# app/config/config.yml

imports:
    ...
    
    - { resource: "@DebrickedSyliusBillogramPlugin/Resources/config/config.yml" }
```

Import routing by adding the following **to the top** of your `app/config/routing.yml` file:

```yaml
# app/config/routing.yml

debricked_sylius_billogram_plugin:
    resource: "@DebrickedSyliusBillogramPlugin/Resources/config/routing.yml"
```

Update your database

```
$ bin/console doctrine:migrations:diff
$ bin/console doctrine:migrations:migrate
```

**Note:** If you are running it on production, add the `-e prod` flag to this command.

## Customization

### Available services you can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) and forms you can [extend](http://symfony.com/doc/current/form/create_form_type_extension.html)

Run the below command to see what Symfony services are shared with this plugin:
 
```bash
$ bin/console debug:container debricked_sylius_billogram_plugin
```

## Testing

```bash
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run gulp
$ bin/console assets:install web -e test
$ bin/console doctrine:database:create -e test
$ bin/console doctrine:schema:create -e test
$ bin/console server:run 127.0.0.1:8080 -d web -e test
$ open http://localhost:8080
$ billogram_api_username="your_sandbox_username" billogram_api_password="your_sandbox_api_key" bin/behat
$ billogram_api_username="your_sandbox_username" billogram_api_password="your_sandbox_api_key" bin/phpspec run
```

## Contribution

Learn more about our contribution workflow on http://docs.sylius.org/en/latest/contributing/.
