# Studio Leismann Utilities

This is a util plugin, containing a collection of feature plugins. 

## Project Overview

* **Purpose:** To develop and maintain a suite of utilities that improve the WordPress sites for editors and developers. All should be considered potential for future candidates for merging into WordPress core.
* **Technologies:** PHP, JavaScript, CSS, a variety of testing and linting tools.

### Project Structure

* `/modules`: A collection of modules. 
* `/includes`: Setup and configuration files for this plugin.

## Building and Running


### Building

* The building process relies heavily on [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) and should be used and followed as close as possible.

* To build the JavaScript and CSS assets: `npm run build`
* To build ZIP files for distribution: `npm run build:zip`


## Code Style

**⚠️ WordPress Core Development Warning:**
Always develop against WordPress core (stable releases) using built-in Block Editor APIs. Do NOT rely on the standalone Gutenberg plugin or experimental/deprecated APIs as they may break in production.

**Code Quality Requirements:**
* Write clean, readable code that follows WordPress conventions
* Write quality code that is functional, secure and perfomant
* No typos in code, comments, or documentation—proofread before committing
* Use descriptive variable and function names that convey intent
* Maintain consistency with existing codebase patterns

In general, the [coding standards for WordPress](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) should be followed:

* [CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
* [HTML Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/html/)
* [JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
* [PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)

Note that for the JavaScript Coding Standards, the code should also be formatted using Prettier, specifically the [wp-prettier](https://www.npmjs.com/package/wp-prettier) fork with the `--paren-spacing` option which inserts extra spaces inside parentheses.

For the HTML Coding Standards, disregard the guidance that void/empty tags should be self-closing, such as `IMG`, `BR`, `LINK`, or `META`. This is only relevant for XML (XHTML), not HTML. So instead of `<br />` this should only use `<br>`, for example.

Additionally, the [inline documentation standards for WordPress](https://developer.wordpress.org/coding-standards/inline-documentation-standards/) should be followed:

* [PHP Documentation Standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/php/)
* [JavaScript Documentation Standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/javascript/)

Note that `lint-staged` will be used to automatically run code quality checks with the tooling based on the staged files.

## Workflow Expectations

* Always update `CHANGELOG.md` with a concise entry whenever functionality or documentation changes are delivered.
* Before starting any new task, create a fresh branch and plan a new pull request dedicated to that task (even if the previous PR is still open or merged).
* Prepare work so it can be merged smoothly: ensure commits are ready for a pull request and include a clear summary of changes and follow-up steps when reporting back to the requester.

### Indentation

In general, indentation should use tabs. Refer to `.editorconfig` in the project root for specifics.

### Inline Documentation

It is expected for new code introduced to have `@since` tags with the `n.e.x.t` placeholder version. It will get replaced with the actual version at the time of release. Do not add any code review comments to such code.

Every file, function, class, method constant, and global variable must have an associated docblock with a `@since` tag.

### PHP

Follow coding conventions in WordPress core. Namespaces are generally not used, as they are not normally used in WordPress core code. Procedural programming patterns are favored where classes play a supporting role, rather than everything being written in OOP.

Whenever possible, the most specific PHP type hints should be used, when backward compatible with PHP 7.2, the minimum version of PHP supported by WordPress and this repository. When native PHP type cannot be used, PHPStan's [PHPDoc Types](https://phpstan.org/writing-php-code/phpdoc-types) should be used, including not only the basic types but also subtypes like `non-empty-string`, [integer ranges](https://phpstan.org/writing-php-code/phpdoc-types#integer-ranges), [general arrays](https://phpstan.org/writing-php-code/phpdoc-types#general-arrays), and especially [array shapes](https://phpstan.org/writing-php-code/phpdoc-types#array-shapes). The types should comply with PHPStan's level 10. The one exception for using PHP types is whenever a function is used as a filter. Since plugins can supply any value at all when filtering, use the expected type with a union to `mixed`. The first statement in the function in this case must always check the type, and if it is not the expected type, override it to be so.

Never render HTML `SCRIPT` tags directly in HTML. Always use the relevant APIs in WordPress for adding scripts, including `wp_enqueue_script()`, `wp_add_inline_script()`, `wp_localize_script()`, `wp_print_script_tag()`, `wp_print_inline_script_tag()`, `wp_enqueue_script_module()` among others. Favor modules over classic scripts.

Here is an example PHP file with various conventions demonstrated.

```php
/**
 * Filtering functions for the Bar plugin.
 *
 * @since n.e.x.t
 * @package Bar
 */

/**
 * Filters post title to be upper case.
 *
 * @since n.e.x.t
 *
 * @param string|mixed $title   Title.
 * @param positive-int $post_id Post ID.
 * @return string Upper-cased title.
 */
function bar_filter_title_uppercase( $title, int $post_id ): string {
	if ( ! is_string( $title ) ) {
		$title = '';
	}
	/**
	 * Because plugins do bad things.
	 *
	 * @var string $title
	 */

	return strtoupper( $title );
}
add_filter( 'the_title', 'bar_filter_title_uppercase', 10, 2 );
```

### JavaScript

All JavaScript code should be written with JSDoc comments. All function parameters, return values, and other types should use [TypeScript in JSDoc](https://www.typescriptlang.org/docs/handbook/jsdoc-supported-types.html).

JavaScript code should be written using ES modules. This JS code must be runnable as-is without having to go through a build step, so it must be plain JavaScript and not TypeScript. The project _may_ also distribute minified versions of these JS files.

Here's an example JS file:

```js
/**
 * Foo module for Optimization Detective
 *
 * This extension optimizes the foo performance feature.
 *
 * @since n.e.x.t
 */

export const name = 'Foo';

/**
 * @typedef {import("web-vitals").LCPMetric} LCPMetric
 * @typedef {import("../optimization-detective/types.ts").InitializeCallback} InitializeCallback
 * @typedef {import("../optimization-detective/types.ts").InitializeArgs} InitializeArgs
 */

/**
 * Initializes extension.
 *
 * @since n.e.x.t
 *
 * @type {InitializeCallback}
 * @param {InitializeArgs} args Args.
 */
export async function initialize( { log, onLCP, extendRootData } ) {
  onLCP(
    ( metric ) => {
      handleLCPMetric( metric, extendRootData, log );
    }
  );
}

// ... function definition for handleLCPMetric omitted ...
```

### CSS
**Use Block Element Modifier (BEM)** for all custom components (WordPress uses BEM for core blocks):
```scss
// Block (component root)
.card { }

// Element (child/part of block) - use double underscore __
.card__heading { }
.card__content { }
.card__image { }

// Modifier (variation) - use double dash --
.card--featured { }
.card--horizontal { }
.card__heading--large { }
```

**For theme-specific components, prefix the block name:**
```scss
// ✅ Correct - BEM with theme prefix
.footheme-hero { }
.footheme-hero__title { }
.footheme-hero--large { }

// ❌ Wrong - no prefix (could conflict with plugins/other themes)
.hero { }
.hero__title { }
```


## Asset Enqueuing Logic

Be wary on where to load files for perfomance and security. 
Examples:
* only load files that are meant for the editors in the backend.
* only enqueues CSS for blocks that present on the current page.



## Writing Style

* Code and documentation should be written professionally, easy to read, avoiding nonsene like fluff or emojis.
* Keep code and docs lightweight but helpful. Prefer explaining intent and constraints over narrating what the code already makes obvious.


## Git workflow:
1. **Create feature branch** from `main`:
   ```bash
   git checkout main
   git pull origin main
   git checkout -b feature/block-style-improvements
   # or: fix/button-accessibility-issue
   # or: enhancement/pattern-library-expansion
   ```

2. **Branch naming** (WordPress standards):
   - `feature/description` - New functionality
   - `fix/description` - Bug fixes  
   - `enhancement/description` - Improvements to existing features
   - Use lowercase with hyphens, be descriptive

3. **Development cycle:**
   - Make changes and compile assets if needed
   - Test in Local by Flywheel environment
   - Commit regularly with clear messages

4. **Create Pull Request:**
   - Push branch: `git push origin feature/your-branch-name`
   - Open PR on GitHub targeting `main` with clear description
   - **If code was primarily generated by AI**: Add at the beginning of the PR description `AI-assisted: Generated with <tool/model>`
   
   ```markdown
   Created with AI
   
   ## What
   Brief description of changes made
   
   ## Why  
   Problem being solved or feature being added
   
   ## Testing Instructions
   Describe how to test the changes.

   ### Testing Instructions for Keyboard
   Describe how to test keyboard accessibility.
   ```


**Commit best practices:**
- Write clear, descriptive commit messages following conventional commits format:
  - `feat: add new block style variation for cards`
  - `fix: correct button focus indicator contrast`
  - `docs: update README with new build instructions`
  - `refactor: simplify asset enqueuing logic`
- Commit compiled files separately from source changes when possible
- Always run `npm run build` before committing CSS/JS changes
- Test in Local by Flywheel before pushing
