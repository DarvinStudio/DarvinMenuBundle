Menu switcher
=============

## How it works

1. Check/uncheck menu checkboxes in your entity's form. Menu items associated with entity will be created in all checked
 menus. Menu items associated with entity will be removed from all unchecked menus. Default menus will be checked
 automatically in new entity form if entity has no parent selected.
 
2. Menu items associated with entity will be removed with entity removal.

## Usage

1. Add field "Darvin\MenuBundle\Form\Type\Admin\MenuSwitcherType" to your entity's form.

You can configure entity's parent input selector using form option "parent_selector".

2. (optional) Configure default menus for your entities:

```yaml
darvin_menu:
    switcher:
        default_menus:
            Darvin\ECommerceBundle\Entity\Product\CatalogInterface: [ main, footer ]
            Darvin\PageBundle\Entity\Page:                          main
```
