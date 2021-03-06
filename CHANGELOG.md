6.7.0: Pass associated object to item extras. It can be accessed for example in Twig using "{{ item.extras.object }}".

6.7.1: Increase priority of the menu switch event subscriber.

6.7.2: Slug map item admin form type: properly sort slug map item choices.

6.7.3: Replace menu matcher with request-based one.

6.8.0: Add entity configuration:

```yaml
darvin_menu:
    entities:

        # Prototype
        entity:

            # Whether to allow to add entities to menu in admin panel
            admin:                true

            # Whether to show entities in slug map children
            slug_children:        true
```

Example:

```yaml
darvin_menu:
    entities:
        AppBundle\Entity\Post:
            slug_children: false
        AppBundle\Entity\PostCategory:
            admin: false
```

6.8.1: Do not consider an ancestor item with empty URI.

6.8.2: Check all translations in menu item validator.

6.8.3: Configure image sizes in admin section config.

6.8.5: Add Menu\ItemTranslation::isEmpty() to override method from translation trait.

6.8.6: Use generic tree sorter in menu item sorter.

6.8.7: Force make services public by default.

6.8.8: Keep menu order in admin sidebar.

6.8.9: Breadcrumbs menu builder: do not reset menu item URL if associated object is hidden.

6.8.11: Pass menu item to extras.

7.0.0: Remove menu switcher from form if no menus configured.

7.0.4: Resolve menu item entity class before instantiating it in switch menu event subscriber.

7.0.5: Admin section: specify filter form's heading field.

7.0.7: Change positions of admin menu items.

7.0.9:

- Move service configs to "services" dir.

- Replace "empty()" calls with null comparisons.

7.0.10: Upgrade vendors.

7.0.12: Use "object" type hint.

7.0.13: Register interfaces for autoconfiguration.

7.0.14: Configure override functionality.

7.0.16: Add "darvin_menu_json()" Twig function:

```twig
{% set header_menu = knp_menu_get('darvin_menu_header', [], {'depth': 2}) %}

{{ darvin_menu_json(header_menu) }}
```

7.0.20: Rework breadcrumbs:

```twig
{# Default breadcrumbs, block "title" will be used as fallback crumb. #}
{{ darvin_menu_breadcrumbs(block('title')) }}

{# Add custom item to the end of breadcrumbs. #}
{{ darvin_menu_breadcrumbs(block('title'), null, null, {(catalog.extraCrumb): null}) }}

{# Add custom item to the beginning of breadcrumbs. #}
{{ darvin_menu_breadcrumbs(block('title'), {'checkout.checkout.breadcrumbs.cart': path('darvin_ecommerce_cart_item')}) }}
```

7.0.21: Allow to cache menus.

7.0.22:
 
- Replace JSON renderer interface with KNP menu's one.

- Register "json" KNP menu renderer.

- Add "renderer" argument to menu controller.

7.0.23: Initialize menu item translations in switch menu event subscriber.

7.0.24: Make private JsonRenderer::toArray() protected.

7.0.25: Extract JSON renderer data collector service.

7.1.0: Add runtime menu builder:

```twig
{{ knp_menu_render(knp_menu_get('darvin_menu_top')) }}
```

equals to

```twig
{{ knp_menu_render(knp_menu_get('darvin_menu', [], {'menu': 'top'})) }}
```

but in the last case alias may be defined in runtime.

7.1.2: Add "menu_title" admin view widget.

8.0.0:

- Use single menu builder for all menus.

- Rename "menu alias" to "menu name".

- Rename entities.

- Move slug map object loader to Content bundle.

- Move slug map item to array admin form data transformer to Content bundle.

- Remove unsupported ar, fr, zh translations.

- Rename "item factory pool" => "item factory registry".

- Replace "slug_map_item_link" admin view widget with the "slug_map_item" one from Content bundle.

- Remove hover image property from menu entry entity.

- Add menu entry entity interface.

- Rename "item factory" => "KNP item factory".

- Replace menu configuration service with menu provider registry one.

8.0.1: Validate builder's "menu" option.

9.0.0: Compatibility with Content bundle 9.
