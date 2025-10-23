# Changelog

## [3.1.0] - 2025-10-23
- Introduced `src/` source tree with automated build output in `build/`.
- Added webpack pipeline that compiles module SCSS to minified CSS and copies PHP/assets.
- Updated bootstrap logic to load compiled files when available while still supporting development mode.

## [3.0.1] - 2025-10-23
- Fixed module autoloading so the Utility Classes panel loads in the editor again.
- Normalised class file names to match the autoloader naming convention.
- Confirmed compiled CSS continues to load in both editor and frontend contexts.


## [3.0.0] - 2025-10-20
- Move from theme to plugin.
---

## Previous Versions

### [2.3.0] - 2025-10-15
- First standalone plugin version
- CSS compilation and caching
- WordPress plugin structure

### [2.2.0] - Previous
- Theme integration version
- Basic Gutenberg panel
- SCSS parsing
