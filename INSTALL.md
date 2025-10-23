# Installation Guide

Quick setup guide for Studio Leismann Utils.

## Requirements

- WordPress 6.3+
- PHP 8.1+

## Installation

1. **Upload** the `studiolsm-utils` folder to `/wp-content/plugins/`
2. **Activate** the plugin in WordPress admin
3. **Configure** at **Settings → Studio Leismann Utils**

That's it! The utility classes panel will appear in the Gutenberg editor.

## First Time Setup

### Configure Modules
Go to **Settings → Studio Leismann Utils** to:
- Enable/disable features
- View module descriptions

### Add Your SCSS File (Optional)
The plugin automatically looks for `studiolsm-helpers.scss` in:
1. Plugin directory (included)
2. Your theme's SCSS folder
3. Custom location (via filter)

## Migrating from Old Plugin

**From "Studio Leismann · Utility Classes Panel":**

1. **Deactivate** the old plugin
2. **Install** this new plugin
3. **Verify** settings at **Settings → Studio Leismann Utils**

All your existing SCSS files will work automatically.

## Troubleshooting

### Plugin Not Working
- Check WordPress 6.3+ and PHP 8.1+
- Clear cache plugins
- Check error logs

### Utility Classes Missing
- Ensure you have a `studiolsm-helpers.scss` file
- Check file permissions
- Reactivate the plugin to clear cache

### Custom SCSS Location
```php
// In functions.php
add_filter('studiolsm_utility_classes_scss_path', function($path) {
    return get_theme_file_path('assets/scss/utilities.scss');
});
```

## What's Included

```
studiolsm-utils/
├── studiolsm-utils.php       # Main plugin
├── README.md                 # Full documentation  
├── modules/
│   └── utility-classes/      # Utility classes module
│       └── assets/
│           ├── css/          # Compiled CSS
│           └── scss/         # Source SCSS
```

## Next Steps

1. **Test** the utility classes panel in Gutenberg
2. **Customize** the SCSS file with your classes
3. **Explore** settings for additional features

Need help? Check the README.md for detailed documentation.