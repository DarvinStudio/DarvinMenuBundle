Menu bundle
===========

This bundle provides menu management functionality for Symfony-based applications.

## Usage

1. Register menu:

```yaml
# config/packages/darvin_menu.yaml
darvin_menu:
    menus:
        header: ~
```

2. Render menu:

```twig
{{ render_esi(controller('darvin_menu.controller.menu', {
    'buildOptions':  {'menu': 'header', 'depth': 2},
    'renderOptions': {'template': 'menu/header.html.twig'},
})) }}
```

## Extras

- [**Menu switcher**](Resources/doc/menu_switcher.md)
