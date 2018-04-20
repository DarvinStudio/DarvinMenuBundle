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
