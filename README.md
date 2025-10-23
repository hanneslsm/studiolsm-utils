# Studio Leismann Utils

Studio Leismann Utils is a modular helper plugin that adds WordPress editor tooling.

## Requirements

- WordPress 6.3 or newer
- PHP 8.1 or newer

## Installation

1. Upload the plugin into `wp-content/plugins/` and activate it.
2. Visit **Settings → Studio Leismann Utils** to review which modules are active.

## Included modules
1. **Utility Classes Panel** – shows all helper classes in the inspector sidebar, lets editors toggle classes, and highlights active selections.



### 1. Utility Classes Panel highlights

- Automatically parses `studiolsm-helpers.scss` to list helper classes and breakpoint variants.
- Provides search and section headings for quick discovery.
- Works in both the editor and on the frontend by enqueueing the compiled CSS.
- Supports lookups in the module assets, legacy plugin folder, and common theme paths, and can be filtered via `studiolsm_utility_classes_scss_path`.

```php
add_filter( 'studiolsm_utility_classes_scss_path', function( $path ) {
    return get_theme_file_path( 'assets/scss/utilities/studiolsm-helpers.scss' );
} );
```

## License

GPL v2 or later
