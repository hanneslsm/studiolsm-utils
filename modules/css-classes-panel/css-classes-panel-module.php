<?php
/**
 * CSS Classes Panel Module for StudiolsmUtils plugin
 *
 * @package StudiolsmUtils\Modules\CssClassesPanel
 */

declare(strict_types=1);

namespace StudiolsmUtils\Modules\CssClassesPanel;

use StudiolsmUtils\Includes\AbstractModule;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CSS Classes Panel module class
 */
class CssClassesPanelModule extends AbstractModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected string $name = 'CSS Classes Panel';

    /**
     * Module version
     *
     * @var string
     */
    protected string $version = '3.0.0';

    /**
     * Initialize the module
     */
    public function init(): void
    {
        $this->add_hooks();
    }

    /**
     * Add WordPress hooks
     */
    protected function add_hooks(): void
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        
        // Also add CSS to wp_head as a fallback
        add_action('wp_head', [$this, 'add_inline_css']);
        add_action('admin_head', [$this, 'add_inline_css']);
        
        // Load text domain for translations
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }

    /**
     * Load text domain for module translations
     */
    public function load_textdomain(): void
    {
        // Module uses the main plugin text domain
        // No additional action needed as main plugin handles this
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_editor_assets(): void
    {
        wp_register_script(
            'studiolsm-class-panel',
            '',
            ['wp-block-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-hooks'],
            $this->version,
            true
        );
        wp_enqueue_script('studiolsm-class-panel');

        wp_add_inline_script(
            'studiolsm-class-panel',
            'window.Studiolsm_ITEMS = ' . wp_json_encode($this->collect_items(), JSON_THROW_ON_ERROR),
            'before'
        );
        wp_add_inline_script('studiolsm-class-panel', $this->inline_js(), 'after');
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets(): void
    {
        $css_content = $this->get_compiled_css();
        if (!empty($css_content)) {
            wp_register_style(
                'studiolsm-utility-classes',
                false,
                [],
                $this->version
            );
            wp_enqueue_style('studiolsm-utility-classes');
            wp_add_inline_style('studiolsm-utility-classes', $css_content);
        }
    }

    /**
     * Enqueue block assets (both editor and frontend)
     */
    public function enqueue_block_assets(): void
    {
        $css_content = $this->get_compiled_css();
        if (!empty($css_content)) {
            wp_register_style(
                'studiolsm-utility-classes-blocks',
                false,
                [],
                $this->version
            );
            wp_enqueue_style('studiolsm-utility-classes-blocks');
            wp_add_inline_style('studiolsm-utility-classes-blocks', $css_content);
        }
    }

    /**
     * Parse helpers.scss and return the flat ITEM list the panel needs.
     * - "Default" tab = all helpers defined outside the responsive mixin.
     * - Every uncommented @include responsive-styles(...) becomes a tab.
     * - Inside each tab, the 3 mixin sections (Display / Order / Alignment)
     *   appear in the order they occur in the SCSS, each followed by its own
     *   classes.
     */
    private function collect_items(): array
    {
        // First, try to find the SCSS file in the module directory
        $module_src = $this->get_module_dir() . 'assets/scss/studiolsm-helpers.scss';
        
        // If not found in module, fall back to old plugin location for backwards compatibility
        if (!is_readable($module_src)) {
            $plugin_src = STUDIOLSM_UTILS_PLUGIN_DIR . '../studiolsm-utility-classes/assets/scss/studiolsm-helpers.scss';
            
            // If not found in old plugin, fall back to theme locations for backwards compatibility
            if (!is_readable($plugin_src)) {
                $theme_src = get_theme_file_path('src/scss/utilities/studiolsm-helpers.scss');
                
                // If not found in theme, try alternative locations
                if (!is_readable($theme_src)) {
                    $theme_src = get_theme_file_path('assets/scss/utilities/studiolsm-helpers.scss');
                }
                
                if (!is_readable($theme_src)) {
                    $theme_src = get_theme_file_path('scss/utilities/studiolsm-helpers.scss');
                }
                
                $module_src = $theme_src;
            } else {
                $module_src = $plugin_src;
            }
        }
        
        // Allow themes to filter the SCSS file path
        $src = apply_filters('studiolsm_utility_classes_scss_path', $module_src);
        
        if (!is_readable($src)) {
            // Return empty array if no SCSS file is found
            return [];
        }

        $lines         = file($src);
        $defaultItems  = [];
        $breakpoints   = [];                 // with-mobile → mobile
        $sections      = [];                 // label → [ 'desc'=>, 'suffixes'=>[] ]
        $sectionOrder  = [];                 // preserve definition order
        $curSection    = null;

        $inDoc       = false;
        $docT        = $docD = null;
        $insideMixin = false;
        $braceDepth  = 0;

        $flush_doc = static function () use (
            &$inDoc, &$docT, &$docD, &$insideMixin,
            &$defaultItems, &$sections, &$sectionOrder, &$curSection
        ): void {
            if ($docT === null && $docD === null) {
                return;
            }
            if ($insideMixin) {
                $label = trim($docT ?? '');
                if ($label !== '') {
                    $sections[$label] = [
                        'desc'     => trim($docD ?? ''),
                        'suffixes' => [],
                    ];
                    $sectionOrder[] = $label;
                    $curSection = $label;
                }
            } else {
                $defaultItems[] = [
                    'type'  => 'heading',
                    'label' => trim($docT ?? ''),
                    'desc'  => trim($docD ?? ''),
                ];
            }
            $docT = $docD = null;
        };

        foreach ($lines as $raw) {
            $line = ltrim($raw);

            /* skip fully commented-out lines -------------------------------- */
            if (str_starts_with($line, '//')) {
                continue;
            }

            /* enter / leave mixin ------------------------------------------- */
            if (preg_match('/@mixin\s+responsive-styles\(/', $line)) {
                $insideMixin = true;
                $braceDepth  = substr_count($line, '{') - substr_count($line, '}');
            } elseif ($insideMixin) {
                $braceDepth += substr_count($line, '{') - substr_count($line, '}');
                if ($braceDepth <= 0) {
                    $insideMixin = false;
                    $curSection  = null;
                }
            }

            /* doc-block capture --------------------------------------------- */
            if (!$inDoc && str_starts_with($line, '/**')) {
                $inDoc = true;
                $docT = $docD = null;
                continue;
            }
            if ($inDoc) {
                if (preg_match('/\*\s*Title:\s*(.+)/', $line, $m)) {
                    $docT = $m[1];
                } elseif (preg_match('/\*\s*Description:\s*(.+)/', $line, $m)) {
                    $docD = $m[1];
                } elseif (str_contains($line, '*/')) {
                    $inDoc = false;
                }
                continue;
            }

            /* breakpoint include (only if not commented-out) ----------------- */
            if (preg_match('/@include\s+responsive-styles\([^,]+,\s*"?(with-[\w-]+)"?/', $line, $m)) {
                $prefix               = $m[1];
                $breakpoints[$prefix] = str_replace('with-', '', $prefix);
                continue;
            }

            /* flush a heading just before its first selector ----------------- */
            if (str_contains($line, '.')) {
                $flush_doc();
            }

            /* suffixes inside the mixin ------------------------------------- */
            if ($insideMixin && $curSection && preg_match('/\.#\{\$prefix}-([\w-]+)/', $line, $m)) {
                $sections[$curSection]['suffixes'][] = $m[1];
                continue;
            }

            /* plain helpers (default tab) ----------------------------------- */
            if (!$insideMixin && preg_match_all('/\.([\w-]+)/', $line, $m)) {
                foreach ($m[1] as $cls) {
                    if (!ctype_digit($cls)) {
                        $defaultItems[] = ['type' => 'class', 'name' => $cls];
                    }
                }
            }
        }
        $flush_doc(); // in case file ends inside a doc-block

        /* assemble ITEM list ------------------------------------------------- */
        $items = $defaultItems;

        foreach ($breakpoints as $prefix => $label) {
            $items[] = ['type' => 'heading', 'label' => $label, 'prefix' => $prefix, 'bp' => true];

            foreach ($sectionOrder as $lbl) {
                $meta = $sections[$lbl];
                $items[] = ['type' => 'heading', 'label' => $lbl, 'desc' => $meta['desc']];

                foreach ($meta['suffixes'] as $suf) {
                    $items[] = ['type' => 'class', 'name' => "{$prefix}-{$suf}"];
                }
            }
        }

        return $items;
    }

    /**
     * Get compiled CSS from SCSS file
     */
    private function get_compiled_css(): string
    {
        // First, try to find pre-compiled CSS in module directory
        $module_css = $this->get_module_dir() . 'assets/css/studiolsm-helpers.css';
        
        if (is_readable($module_css)) {
            // Check for cached CSS
            $cache_key = 'studiolsm_utility_css_precompiled_' . md5($module_css . filemtime($module_css));
            $cached_css = get_transient($cache_key);
            
            if ($cached_css !== false) {
                return $cached_css;
            }
            
            // Read the pre-compiled CSS
            $css_content = file_get_contents($module_css);
            
            // Cache the CSS for 1 hour
            set_transient($cache_key, $css_content, HOUR_IN_SECONDS);
            
            return $css_content;
        }
        
        // Fall back to SCSS compilation
        // First, try to find the SCSS file in the module directory
        $module_src = $this->get_module_dir() . 'assets/scss/studiolsm-helpers.scss';
        
        // If not found in module, fall back to old plugin location for backwards compatibility
        if (!is_readable($module_src)) {
            $plugin_src = STUDIOLSM_UTILS_PLUGIN_DIR . '../studiolsm-utility-classes/assets/scss/studiolsm-helpers.scss';
            
            // If not found in old plugin, fall back to theme locations for backwards compatibility
            if (!is_readable($plugin_src)) {
                $theme_src = get_theme_file_path('src/scss/utilities/studiolsm-helpers.scss');
                
                // If not found in theme, try alternative locations
                if (!is_readable($theme_src)) {
                    $theme_src = get_theme_file_path('assets/scss/utilities/studiolsm-helpers.scss');
                }
                
                if (!is_readable($theme_src)) {
                    $theme_src = get_theme_file_path('scss/utilities/studiolsm-helpers.scss');
                }
                
                $module_src = $theme_src;
            } else {
                $module_src = $plugin_src;
            }
        }
        
        // Allow themes to filter the SCSS file path
        $src = apply_filters('studiolsm_utility_classes_scss_path', $module_src);
        
        if (!is_readable($src)) {
            // Return empty string if no SCSS file is found
            return '';
        }

        // Check for cached CSS
        $cache_key = 'studiolsm_utility_css_' . md5($src . filemtime($src));
        $cached_css = get_transient($cache_key);
        
        if ($cached_css !== false) {
            return $cached_css;
        }

        // Compile SCSS to CSS
        $css_content = $this->compile_scss_to_css($src);
        
        // Cache the compiled CSS for 1 hour
        set_transient($cache_key, $css_content, HOUR_IN_SECONDS);
        
        return $css_content;
    }

    /**
     * Compile SCSS file to CSS
     */
    private function compile_scss_to_css(string $scss_file): string
    {
        $lines = file($scss_file);
        $css_output = '';
        $current_selector = '';
        $current_rules = [];
        $responsive_blocks = [];
        $breakpoints = [
            'mobile' => '@media (max-width: 767px)',
            'medium' => '@media (min-width: 768px) and (max-width: 1023px)', 
            'large' => '@media (min-width: 1024px)',
            'xl' => '@media (min-width: 1280px)',
        ];
        
        $inside_mixin = false;
        $current_prefix = '';
        $mixin_css = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '//') || str_starts_with($line, '/*')) {
                continue;
            }
            
            // Handle responsive mixin includes
            if (preg_match('/@include\s+responsive-styles\([^,]+,\s*"?(with-[\w-]+)"?\)\s*\{/', $line, $matches)) {
                $current_prefix = $matches[1];
                $prefix_key = str_replace('with-', '', $current_prefix);
                $inside_mixin = true;
                $mixin_css = '';
                continue;
            }
            
            // Handle mixin end
            if ($inside_mixin && $line === '}') {
                if (isset($breakpoints[$prefix_key])) {
                    $responsive_blocks[$prefix_key] = $mixin_css;
                }
                $inside_mixin = false;
                $current_prefix = '';
                continue;
            }
            
            // Handle CSS rules inside mixin
            if ($inside_mixin) {
                // Replace #{$prefix} with actual prefix
                $processed_line = str_replace('#{$prefix}', $current_prefix, $line);
                $mixin_css .= $processed_line . "\n";
                continue;
            }
            
            // Handle regular CSS rules (outside mixin)
            if (preg_match('/^\.([^{]+)\s*\{/', $line, $matches)) {
                // Output previous selector if exists
                if (!empty($current_selector) && !empty($current_rules)) {
                    $css_output .= ".$current_selector { " . implode(' ', $current_rules) . " }\n";
                }
                
                $current_selector = trim($matches[1]);
                $current_rules = [];
            } elseif (preg_match('/([^:]+):\s*([^;]+);/', $line, $matches)) {
                $current_rules[] = trim($matches[1]) . ': ' . trim($matches[2]) . ';';
            } elseif ($line === '}' && !empty($current_selector)) {
                if (!empty($current_rules)) {
                    $css_output .= ".$current_selector { " . implode(' ', $current_rules) . " }\n";
                }
                $current_selector = '';
                $current_rules = [];
            }
        }
        
        // Output any remaining selector
        if (!empty($current_selector) && !empty($current_rules)) {
            $css_output .= ".$current_selector { " . implode(' ', $current_rules) . " }\n";
        }
        
        // Add responsive blocks
        foreach ($responsive_blocks as $key => $css) {
            if (isset($breakpoints[$key]) && !empty($css)) {
                $css_output .= "\n" . $breakpoints[$key] . " {\n" . $css . "}\n";
            }
        }
        
        return $css_output;
    }

    /**
     * Add inline CSS directly to head (fallback method)
     */
    public function add_inline_css(): void
    {
        $css_content = $this->get_compiled_css();
        if (!empty($css_content)) {
            echo '<style id="studiolsm-utility-classes-inline">' . "\n";
            echo $css_content;
            echo '</style>' . "\n";
        }
    }

    /**
     * Return the inline JavaScript for the React component
     */
    private function inline_js(): string
    {
        return <<<'JS'
(() => {
	const { createElement: el, Fragment, useState } = wp.element;
	const {
		PanelBody, ToggleControl, SearchControl, TabPanel, BaseControl
	} = wp.components;
	const { InspectorControls } = wp.blockEditor;
	const { useSelect, useDispatch } = wp.data;
	const { addFilter } = wp.hooks;

	const ITEMS = Array.isArray( window.Studiolsm_ITEMS ) ? window.Studiolsm_ITEMS : [];

	/* one-time CSS ------------------------------------------------------- */
	if ( ! document.getElementById( 'studiolsm-style' ) ) {
		const s = document.createElement( 'style' );
		s.id = 'studiolsm-style';
		s.textContent = `
			.studiolsm-tabs .components-tab-panel__tabs{
				display:flex;flex-wrap:nowrap;gap:4px;
				overflow-x:auto;scrollbar-width:thin;margin-bottom:14px;
			}
			.studiolsm-tabs .components-tab-panel__tabs::-webkit-scrollbar{height:6px}
			.studiolsm-tabs .components-tab-panel__tabs button{
				white-space:nowrap;word-break:keep-all;
			}`;
		document.head.appendChild( s );
	}

	/* build tabsMap ------------------------------------------------------ */
	const tabsMap = new Map( [ [ 'default', { title:'Default', items:[] } ] ] );
	let current = 'default';

	for ( const it of ITEMS ) {
		if ( it.bp ) {
			current = it.prefix;
			tabsMap.set( current, { title:it.label, items:[] } );
			continue;
		}
		tabsMap.get( current ).items.push( it );
	}

	/* helpers ------------------------------------------------------------ */
	const Dot = () => el('span',{style:{
		display:'inline-block',width:'0.45em',height:'0.45em',
		borderRadius:'50%',background:'var(--wp-admin-theme-color,#007cba)'
	}});

		const Section = ({ title, desc, uniq }) =>
			el( BaseControl, {
				key: uniq,
				label: title,
				help:  desc || null,
				className: 'studiolsm-section-label',
				__nextHasNoMarginBottom: true,
			});

		const shortLabel = (cls, tab) =>
			cls.startsWith('with-')
				? ( tab !== 'default' && cls.startsWith(tab + '-') )
					? '…' + cls.slice( tab.length + 1 )
					: '…' + cls.slice(5)
				: cls;	/* component ---------------------------------------------------------- */
	const ClassPanel = ({ clientId }) => {
		const { className = '' } = useSelect( sel =>
			sel('core/block-editor').getBlockAttributes( clientId ) );
		const { updateBlockAttributes } = useDispatch('core/block-editor');

		const activeSet   = new Set( className.split(/\s+/).filter(Boolean) );
		const [q, setQ]   = useState('');

		const toggle = cls => {
			activeSet.has(cls) ? activeSet.delete(cls) : activeSet.add(cls);
			updateBlockAttributes( clientId, { className:[...activeSet].join(' ') } );
		};

		const tabs = Array.from( tabsMap, ([ name, obj ]) => {
			const has = obj.items.some(it => it.type==='class' && activeSet.has(it.name));
			return {
				name,
				title: el('span',{
					style:{display:'flex',alignItems:'center',gap:'0.25em'}
				},[ obj.title, has ? el(Dot) : null ]),
			};
		});

		const header = el('span',{
			style:{display:'flex',alignItems:'center',gap:'0.4em'}
		},[ 'CSS classes', activeSet.size ? el(Dot) : null ]);

		const renderTab = tab => {
			const pool = tabsMap.get(tab.name).items;
			const list = q
				? pool.filter(it =>
						it.type==='class'
							? it.name.toLowerCase().includes(q.toLowerCase())
							: it.label.toLowerCase().includes(q.toLowerCase()))
				: pool;

			return el(Fragment,null,
				list.length
					? list.map(it =>
							it.type==='heading'
								? Section({ title:it.label, desc:it.desc, uniq:it.label + tab.name })
								: el(ToggleControl,{
										key:it.name,
										label:shortLabel(it.name,tab.name),
										checked:activeSet.has(it.name),
										onChange:()=>toggle(it.name),
								  }))
					: el('p',{style:{opacity:0.6}},'No matches')
			);
		};

		return el(InspectorControls,{group:'settings'},
			el(PanelBody,{title:header,initialOpen:false},
				el(SearchControl,{
					value:q,
					placeholder:'Search classes…',
					onChange:setQ,
					__nextHasNoMarginBottom:true,
				}),
				el(TabPanel,{className:'studiolsm-tabs',tabs},renderTab)
			)
		);
	};

	/* HOC --------------------------------------------------------------- */
	addFilter(
		'editor.BlockEdit','studiolsm/utility-panel',
		BlockEdit => props =>
			props.clientId
				? el(Fragment,null,el(BlockEdit,props),el(ClassPanel,{clientId:props.clientId}))
				: el(BlockEdit,props),
		20
	);
})();
JS;
    }
}
