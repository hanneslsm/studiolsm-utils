# Studio Leismann Utils

A modular WordPress plugin that adds utility tools for WordPress development.

## What it does

- **Utility Classes Panel**: Adds a Gutenberg panel for applying CSS utility classes
- **Modular System**: Enable/disable features as needed
- **Auto CSS Compilation**: Converts SCSS to CSS automatically
- **No Setup Required**: Works out of the box

## Quick Start

1. **Install**: Upload to `/wp-content/plugins/` and activate
2. **Configure**: Go to **Settings → Studio Leismann Utils**
3. **Use**: The utility classes panel appears in Gutenberg editor

## Requirements

- WordPress 6.3+
- PHP 8.1+

## Features

### Utility Classes Panel
- Automatically reads your SCSS utility classes
- Organizes classes by breakpoints (Mobile, Tablet, Desktop)
- Search functionality to find classes quickly
- Visual indicators show active classes
- Works in both editor and frontend

### How it works
The plugin looks for `studiolsm-helpers.scss` in these locations:
1. Plugin directory (recommended)
2. Your theme's SCSS folder
3. Custom path (using filter)

## SCSS Format

```scss
// Global classes (Default tab)
.text-center { text-align: center; }
.hidden { display: none; }

// Responsive classes (creates tabs)
@include responsive-styles($breakpoint-mobile, "with-mobile") {
    /**
     * Title: Display
     * Description: Show/hide elements
     */
    .#{$prefix}-block { display: block; }
    .#{$prefix}-none { display: none; }
}
```

## Customization

### Custom SCSS Location
```php
add_filter('studiolsm_utility_classes_scss_path', function($path) {
    return get_theme_file_path('assets/scss/utilities.scss');
});
```

### Disable Modules
Go to **Settings → Studio Leismann Utils** to enable/disable features.

## Version 3.0.0

Complete rewrite with modular architecture. Migrating from the old plugin? It will automatically detect your existing SCSS files.

## License

GPL v2 or later