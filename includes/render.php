<?php
/**
 * Builds and renders the ecosystem bar.
 *
 * Everything is printed inline (one <style> block + inline SVG logos read from
 * assets/logos/). No assets are enqueued, no HTTP requests are made, and no
 * JavaScript is used — the dropdown is a native <details> element.
 *
 * Configurable per shortcode call (see keb_shortcode): background color, container
 * width / full width, alignment, bar height, and fixed-vs-inline placement.
 *
 * @package Kodezen_Ecosystem_Bar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the tiny enhancement stylesheet once per request.
 *
 * All appearance is applied as inline style="" attributes on the elements
 * themselves (see keb_build_bar), so the bar renders correctly even if this
 * <style> block is stripped by a page builder. This block only carries the few
 * things inline CSS cannot express: hover / focus states, the native <details>
 * marker removal, and the caret rotation when the dropdown is open.
 */
function keb_print_styles() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;
	?>
<style id="keb-styles">
.keb-bar a:hover{background:var(--keb-hover,rgba(0,0,0,.05))}
.keb-bar a[aria-current="page"]:hover{filter:brightness(.98)}
.keb-bar a:focus-visible,.keb-more>summary:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(0,107,255,.25)}
.keb-more>summary::-webkit-details-marker{display:none}
.keb-more>summary:hover,.keb-more[open]>summary{filter:brightness(.96)}
.keb-more[open] .keb-caret{transform:rotate(180deg)}
.keb-more .keb-menu a:hover{background:var(--keb-hover,#f3f4f6)}
.keb-more .keb-menu a.keb-bundle{transition:transform .15s ease,box-shadow .15s ease}
.keb-more .keb-menu a.keb-bundle:hover{background:linear-gradient(135deg,#005fe6 0%,#4e24e6 55%,#7e18e6 100%)!important;transform:translateY(-1px);box-shadow:0 10px 24px rgba(91,43,255,.36)}
@media(max-width:781.98px){.keb-bar.keb-hide-mobile{display:none!important}}
@media(max-width:1023.98px){.keb-bar.keb-desktop-only{display:none!important}}
</style>
	<?php
}

/**
 * Read an inline SVG logo from assets/logos/, normalized for CSS sizing.
 *
 * Cached per slug for the request. Fixed width/height on the root <svg> are
 * stripped and a viewBox injected when missing, and the rendered height is set so
 * width scales by aspect ratio. Returns '' when the file is unavailable.
 *
 * @param string $slug   Logo file slug (without extension).
 * @param int    $height Rendered logo height in px.
 * @return string Inline SVG markup, or '' if not found.
 */
function keb_logo_svg( $slug, $height ) {
	static $cache = array();
	$key = $slug . '@' . $height;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$file = KEB_PATH . 'assets/logos/' . $slug . '.svg';
	if ( ! preg_match( '/^[a-z0-9\-]+$/', $slug ) || ! is_readable( $file ) ) {
		$cache[ $key ] = '';
		return '';
	}

	$svg = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$pos = stripos( $svg, '<svg' );
	if ( false === $pos ) {
		$cache[ $key ] = '';
		return '';
	}
	$svg = substr( $svg, $pos );

	// Inject a viewBox from width/height when the root <svg> lacks one.
	if ( ! preg_match( '/<svg[^>]*\sviewBox=/i', $svg )
		&& preg_match( '/<svg[^>]*\swidth="([\d.]+)"/i', $svg, $w )
		&& preg_match( '/<svg[^>]*\sheight="([\d.]+)"/i', $svg, $h ) ) {
		$svg = preg_replace( '/<svg/i', '<svg viewBox="0 0 ' . $w[1] . ' ' . $h[1] . '"', $svg, 1 );
	}

	// Strip fixed width/height on the root <svg> so our height drives the size.
	$svg = preg_replace( '/(<svg\b[^>]*?)\swidth="[^"]*"/i', '$1', $svg, 1 );
	$svg = preg_replace( '/(<svg\b[^>]*?)\sheight="[^"]*"/i', '$1', $svg, 1 );
	$svg = preg_replace(
		'/<svg\b/i',
		'<svg class="keb-logo" aria-hidden="true" focusable="false" style="display:block;width:auto;height:' . (int) $height . 'px"',
		$svg,
		1
	);

	$cache[ $key ] = $svg;
	return $svg;
}

/**
 * Build the markup for a single product link.
 *
 * @param array  $p          Product config entry.
 * @param int    $height     Logo height in px.
 * @param string $link_attrs Extra <a> attributes (e.g. target/rel). Pre-escaped.
 * @param string $style      Inline style for the <a>. Pre-built.
 * @return string
 */
function keb_link_html( array $p, $height, $link_attrs = '', $style = '' ) {
	$svg   = ! empty( $p['logo'] ) ? keb_logo_svg( $p['logo'], $height ) : '';
	$inner = ( '' !== $svg )
		? $svg // Trusted, plugin-authored SVG (see keb_logo_svg / assets/logos).
		: '<span style="font-weight:600;color:#141a24">' . esc_html( $p['name'] ) . '</span>';

	return sprintf(
		'<a href="%1$s"%2$s title="%3$s" aria-label="%3$s" style="%4$s">%5$s</a>',
		esc_url( $p['url'] ),
		$link_attrs,
		esc_attr( $p['name'] ),
		esc_attr( $style ),
		$inner
	);
}

/**
 * Normalize a CSS length/color-ish value for safe inline-style output.
 *
 * @param string $value Raw attribute value.
 * @return string Sanitized value (empty if it looks unsafe).
 */
function keb_safe_css( $value ) {
	$value = trim( (string) $value );
	// Allow hex colors, rgb(a), common color words, and lengths like 1200px / 90% / 100vw.
	if ( preg_match( '/^#[0-9a-f]{3,8}$/i', $value )
		|| preg_match( '/^rgba?\([\d.,\s%]+\)$/i', $value )
		|| preg_match( '/^[a-z]+$/i', $value )
		|| preg_match( '/^[\d.]+(px|rem|em|%|vw)$/i', $value ) ) {
		return $value;
	}
	return '';
}

/**
 * Split a URL into a normalized [host, path] pair (scheme + www stripped,
 * lowercased, trailing slash removed).
 *
 * @param string $url URL or host/path string.
 * @return array [ host, path ]
 */
function keb_split_url( $url ) {
	$s = strtolower( trim( (string) $url ) );
	$s = preg_replace( '#^https?://#', '', $s );
	$s = preg_replace( '#^www\.#', '', $s );
	$s = strtok( $s, '?#' ); // Drop query/fragment.
	$slash = strpos( $s, '/' );
	if ( false === $slash ) {
		return array( $s, '' );
	}
	return array( substr( $s, 0, $slash ), rtrim( substr( $s, $slash ), '/' ) );
}

/**
 * Detect which product matches the current request (longest URL prefix on the
 * same host wins), so the bar can mark that item as active/selected.
 *
 * @param array $products Product list.
 * @return int Index of the active product, or -1 if none.
 */
function keb_detect_active_index( array $products ) {
	$host = '';
	if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
		$host = function_exists( 'sanitize_text_field' )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) )
			: (string) $_SERVER['HTTP_HOST'];
	} elseif ( function_exists( 'home_url' ) ) {
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	}
	$uri = '/';
	if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
		$uri = function_exists( 'sanitize_text_field' )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: (string) $_SERVER['REQUEST_URI'];
	}

	$chost = strtolower( preg_replace( '#^www\.#', '', $host ) );
	$cpath = rtrim( strtok( $uri, '?#' ), '/' );

	$best     = -1;
	$best_len = -1;
	foreach ( $products as $i => $p ) {
		if ( empty( $p['url'] ) ) {
			continue;
		}
		list( $ph, $pp ) = keb_split_url( $p['url'] );
		if ( '' === $ph || $ph !== $chost ) {
			continue;
		}
		if ( '' === $pp ) {
			$len = 0; // Root product: matches any path on this host.
		} elseif ( $cpath === $pp || 0 === strpos( $cpath . '/', $pp . '/' ) ) {
			$len = strlen( $pp );
		} else {
			continue;
		}
		if ( $len > $best_len ) {
			$best_len = $len;
			$best     = $i;
		}
	}
	return $best;
}

/**
 * Build the full ecosystem bar markup (styles + bar).
 *
 * @param array $args {
 *     @type bool   $inline True => rendered in place; false => fixed to the top.
 *     @type string $bg     Bar background color. Default '#ffffff'.
 *     @type string $width  'full' for edge-to-edge, or a max-width (e.g. '1200px')
 *                          to center the content. Default 'full'.
 *     @type string $align  'left' | 'center' | 'right'. Default 'left'.
 *     @type int    $height Bar height in px. Default 56.
 * }
 * @return string HTML, or '' when there are no products.
 */
function keb_build_bar( array $args = array() ) {
	$args = array_merge(
		array(
			'inline'          => false,
			'bg'              => '#ffffff',
			'width'           => 'full',
			'align'           => 'justify',
			'height'          => 56,
			'new_tab'         => false,
			// Hover + active (current product) styling.
			'hover_bg'         => '#f0f1f3',
			'active_highlight' => true,
			'active_bg'        => '#eef2ff',
			'active_indicator' => '#006bff',
			'active_index'     => null, // null = auto-detect; int = force (editor preview).
			// Bar bottom border.
			'border_color'    => '#e5e7eb',
			'border_width'    => 1,
			// Device visibility: 'all' | 'no-mobile' | 'desktop-only'.
			'visibility'      => 'no-mobile',
			// More button.
			'more_label'      => 'More',
			'more_show_icon'  => true,
			'more_text'       => '#141a24',
			'more_bg'         => '#ffffff',
			'more_border'     => '#d9dce2',
			'more_border_w'   => 1,
			'more_icon_color' => '#006bff',
		),
		$args
	);

	$products = require KEB_PATH . 'includes/products.php';
	if ( empty( $products ) || ! is_array( $products ) ) {
		return '';
	}

	$important = array_filter( $products, static fn( $p ) => ! empty( $p['important'] ) );
	$more      = array_filter( $products, static fn( $p ) => empty( $p['important'] ) );

	// Derive styles from args.
	$bar_h    = max( 36, (int) $args['height'] );
	$logo_h   = max( 14, $bar_h - 38 );
	$bg       = keb_safe_css( $args['bg'] );
	$bg       = '' !== $bg ? $bg : '#ffffff';
	$align    = in_array( $args['align'], array( 'left', 'center', 'right', 'justify' ), true ) ? $args['align'] : 'justify';
	$justify  = array(
		'left'    => 'flex-start',
		'center'  => 'center',
		'right'   => 'flex-end',
		'justify' => 'flex-start',
	)[ $align ];
	// Width of the inner logo container:
	//  - full    => spans its parent (no max-width).
	//  - content => theme content size (theme.json contentSize).
	//  - wide    => theme wide size (theme.json wideSize).
	//  - <length> => a custom max-width like 1140px / 90%.
	$w_raw   = strtolower( trim( (string) $args['width'] ) );
	$is_full = ( 'full' === $w_raw );
	if ( $is_full ) {
		$max_w = '';
	} elseif ( 'content' === $w_raw ) {
		$max_w = 'var(--wp--style--global--content-size, 1140px)';
	} elseif ( 'wide' === $w_raw ) {
		$max_w = 'var(--wp--style--global--wide-size, 1280px)';
	} else {
		$max_w = keb_safe_css( $args['width'] );
	}

	// Bar bottom border.
	$bord_c  = keb_safe_css( $args['border_color'] );
	$bord_c  = '' !== $bord_c ? $bord_c : '#e5e7eb';
	$bord_w  = max( 0, (int) $args['border_width'] );
	$border_css = $bord_w > 0 ? sprintf( 'border-bottom:%dpx solid %s', $bord_w, $bord_c ) : 'border-bottom:0';

	// Device visibility class (media queries live in keb_print_styles).
	$vis       = in_array( $args['visibility'], array( 'all', 'no-mobile', 'desktop-only' ), true ) ? $args['visibility'] : 'no-mobile';
	$vis_class = 'no-mobile' === $vis ? ' keb-hide-mobile' : ( 'desktop-only' === $vis ? ' keb-desktop-only' : '' );

	// Hover + active styling.
	$hover_bg = keb_safe_css( $args['hover_bg'] );
	$hover_bg = '' !== $hover_bg ? $hover_bg : 'rgba(0,0,0,.05)';
	$active_bg = keb_safe_css( $args['active_bg'] );
	$active_bg = '' !== $active_bg ? $active_bg : '#eef2ff';
	$active_in = keb_safe_css( $args['active_indicator'] );
	$active_in = '' !== $active_in ? $active_in : '#006bff';

	// Resolve which item is active: explicit override, else auto-detect, else none.
	$active_index = $args['active_index'];
	if ( null === $active_index ) {
		$active_index = ! empty( $args['active_highlight'] ) ? keb_detect_active_index( $products ) : -1;
	}
	$active_index = (int) $active_index;

	$font = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif';

	// Full appearance is inlined per element so the bar survives a stripped <style>.
	$position   = $args['inline'] ? '' : 'position:fixed;top:0;left:0;right:0;z-index:99999;';
	$bar_style  = $position . sprintf(
		'margin:0;display:flex;align-items:center;width:100%%;box-sizing:border-box;%s;font-family:%s;font-size:13px;line-height:1;background:%s;height:%dpx;--keb-hover:%s',
		$border_css,
		$font,
		$bg,
		$bar_h,
		$hover_bg
	);
	$inner_style = sprintf( 'display:flex;align-items:center;gap:4px;width:100%%;margin:0 auto;padding:0 14px;box-sizing:border-box;justify-content:%s', $justify );
	if ( '' !== $max_w ) {
		$inner_style .= sprintf( ';max-width:%s', $max_w );
	}
	// Row behavior per alignment:
	//  - justify: fill the width and space items out evenly (space-between).
	//  - left:    fill the width, items packed to the start.
	//  - center/right: size to content so the inner container can position it.
	if ( 'justify' === $align ) {
		$scroll_style = 'display:flex;align-items:center;flex-wrap:wrap;gap:10px;flex:1;min-width:0;justify-content:space-between';
	} elseif ( 'left' === $align ) {
		$scroll_style = 'display:flex;align-items:center;flex-wrap:wrap;gap:2px;flex:1;min-width:0';
	} else {
		$scroll_style = 'display:flex;align-items:center;flex-wrap:wrap;gap:2px;flex:0 1 auto';
	}

	// More button colors (sanitized, with fallbacks).
	$more_text  = keb_safe_css( $args['more_text'] );
	$more_text  = '' !== $more_text ? $more_text : '#141a24';
	$more_bg    = keb_safe_css( $args['more_bg'] );
	$more_bg    = '' !== $more_bg ? $more_bg : '#ffffff';
	$more_bord  = keb_safe_css( $args['more_border'] );
	$more_bord  = '' !== $more_bord ? $more_bord : '#d9dce2';
	$more_ic    = keb_safe_css( $args['more_icon_color'] );
	$more_ic    = '' !== $more_ic ? $more_ic : '#006bff';
	$more_label = trim( (string) $args['more_label'] );
	$more_label = '' !== $more_label ? $more_label : 'More';
	$show_icon  = ! empty( $args['more_show_icon'] );
	$more_bw    = max( 0, (int) $args['more_border_w'] );
	$more_border_css = $more_bw > 0 ? sprintf( 'border:%dpx solid %s', $more_bw, $more_bord ) : 'border:0';

	$link_style      = 'display:inline-flex;align-items:center;padding:6px 10px;border-radius:8px;white-space:nowrap;text-decoration:none;transition:background .15s ease';
	$menu_link_style = 'display:inline-flex;align-items:center;padding:9px 12px;border-radius:9px;white-space:nowrap;text-decoration:none;transition:background .15s ease';
	// Active (current product) styles: highlight background + bottom indicator bar.
	$active_extra       = sprintf( ';background:%s;box-shadow:inset 0 -2px 0 %s', $active_bg, $active_in );
	$active_link_style  = $link_style . $active_extra;
	$active_menu_style  = $menu_link_style . $active_extra;
	$more_style      = 'position:relative;flex:0 0 auto;margin-left:6px';
	$summary_style   = sprintf(
		'list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:7px;font-weight:600;padding:8px 14px;border-radius:999px;white-space:nowrap;transition:filter .15s ease,box-shadow .15s ease;color:%s;background:%s;%s',
		$more_text,
		$more_bg,
		$more_border_css
	);
	$apps_style      = 'display:block;flex:0 0 auto;color:' . $more_ic;
	$caret_style     = 'display:block;flex:0 0 auto;transition:transform .18s ease;color:' . $more_text;
	$menu_style      = 'position:absolute;top:calc(100% + 8px);right:0;min-width:280px;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:7px;box-shadow:0 16px 40px rgba(16,24,40,.16);display:flex;flex-direction:column;gap:1px;z-index:100000';
	$head_style      = 'margin:4px 12px 6px;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#98a1b0';

	// Same tab by default; opt into a new tab with new_tab=yes.
	$link_attrs = $args['new_tab'] ? ' target="_blank" rel="noopener"' : '';

	ob_start();
	keb_print_styles();
	?>
<div class="keb-bar<?php echo esc_attr( $vis_class ); ?>" role="navigation" aria-label="Kodezen ecosystem" style="<?php echo esc_attr( $bar_style ); ?>">
	<div class="keb-inner" style="<?php echo esc_attr( $inner_style ); ?>">
		<div class="keb-scroll" style="<?php echo esc_attr( $scroll_style ); ?>">
			<?php
			foreach ( $important as $idx => $p ) {
				$is_active = ( $idx === $active_index );
				echo keb_link_html(
					$p,
					$logo_h,
					$link_attrs . ( $is_active ? ' aria-current="page"' : '' ),
					$is_active ? $active_link_style : $link_style
				);
			}
			?>
		</div>
		<?php if ( ! empty( $more ) ) : ?>
		<details class="keb-more" style="<?php echo esc_attr( $more_style ); ?>">
			<summary aria-label="<?php echo esc_attr( $more_label ); ?>" style="<?php echo esc_attr( $summary_style ); ?>">
				<?php if ( $show_icon ) : ?>
				<svg class="keb-apps" width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" style="<?php echo esc_attr( $apps_style ); ?>"><rect x="0" y="0" width="6" height="6" rx="1.6" fill="currentColor"/><rect x="9" y="0" width="6" height="6" rx="1.6" fill="currentColor"/><rect x="0" y="9" width="6" height="6" rx="1.6" fill="currentColor"/><rect x="9" y="9" width="6" height="6" rx="1.6" fill="currentColor"/></svg>
				<?php endif; ?>
				<span><?php echo esc_html( $more_label ); ?></span>
				<span class="keb-more-count" style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 6px;border-radius:999px;background:<?php echo esc_attr( $more_ic ); ?>;color:#fff;font-size:11px;font-weight:700;line-height:1"><?php echo esc_html( (string) count( $more ) ); ?></span>
				<svg class="keb-caret" width="11" height="11" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" style="<?php echo esc_attr( $caret_style ); ?>"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</summary>
			<div class="keb-menu" style="<?php echo esc_attr( $menu_style ); ?>">
				<a class="keb-bundle" href="https://kodezen.com/kodezen-ecosystem/"<?php echo $args['new_tab'] ? ' target="_blank" rel="noopener"' : ''; ?> style="position:relative;overflow:hidden;display:flex;align-items:center;gap:12px;margin:2px 2px 8px;padding:12px 14px;border-radius:11px;text-decoration:none;background:linear-gradient(135deg,#006bff 0%,#5b2bff 55%,#8e1cff 100%);box-shadow:0 6px 18px rgba(91,43,255,.28)">
					<span aria-hidden="true" style="position:absolute;right:-24px;top:-24px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.08)"></span>
					<span aria-hidden="true" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;flex:0 0 auto;border-radius:9px;background:rgba(255,255,255,.18);color:#fff">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M12 2 3 6.5v11L12 22l9-4.5v-11L12 2Zm0 2.3 6.8 3.4L12 11.1 5.2 7.7 12 4.3Zm-7 5.1 6 3v8.3l-6-3v-8.3Zm8 11.3v-8.3l6-3v8.3l-6 3Z" fill="currentColor"/></svg>
					</span>
					<span style="display:flex;flex-direction:column;gap:2px;min-width:0;flex:1 1 auto">
						<span style="display:flex;align-items:center;gap:7px;font-weight:700;color:#fff;font-size:13px;line-height:1.2">Kodezen Bundle<span style="display:inline-flex;align-items:center;padding:1px 7px;border-radius:999px;background:#ffd84d;color:#1a1300;font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase">Save</span></span>
						<span style="color:rgba(255,255,255,.85);font-size:11.5px;line-height:1.35">All 14 plugins · One license · Lifetime deal</span>
					</span>
					<span style="color:#fff;font-size:16px;line-height:1;flex:0 0 auto">→</span>
				</a>
				<p class="keb-menu-head" style="<?php echo esc_attr( $head_style ); ?>">Explore the ecosystem</p>
				<?php
				foreach ( $more as $idx => $p ) {
					$is_active = ( $idx === $active_index );
					echo keb_link_html(
						$p,
						$logo_h,
						$link_attrs . ( $is_active ? ' aria-current="page"' : '' ),
						$is_active ? $active_menu_style : $menu_link_style
					);
				}
				?>
			</div>
		</details>
		<?php endif; ?>
	</div>
</div>
	<?php
	return ob_get_clean();
}

/**
 * Auto-render callback for the wp_body_open / wp_footer hooks (fixed bar).
 * Guards against running twice when both hooks fire.
 */
function keb_render_bar() {
	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;

	// Trusted, plugin-authored markup (see keb_build_bar).
	echo keb_build_bar( array( 'inline' => false ) );
}

/**
 * Shortcode handler: [kodezen_ecosystem_bar].
 *
 * Attributes:
 *   inline="yes|no"   Render in place (default yes) or as a fixed top bar (no).
 *   bg="#ffffff"      Bar background color (hex / rgb / color word).
 *   width="full"      'full' (edge-to-edge), 'content' (theme content size),
 *                     'wide' (theme wide size), or a max-width like "1200px".
 *   align="justify"   justify (spread evenly, default) | left | center | right.
 *   height="56"       Bar height in px.
 *   new_tab="no"      Open links in a new tab (yes) or the same tab (no, default).
 *   hover_bg="#f0f1f3"      Hover background for logo links.
 *   active_highlight="yes"  Highlight the product matching the current site.
 *   active_bg="#eef2ff"     Active item background.
 *   active_indicator="#006bff" Active item underline color.
 *   border_color="#e5e7eb"  Bar bottom border color.
 *   border_width="1"        Bar bottom border width in px (0 = none).
 *   visibility="no-mobile"  all | no-mobile (hide < 782px) | desktop-only (hide < 1024px).
 *   more_label="More"       Text on the More button.
 *   more_icon="yes"         Show the apps icon on the More button.
 *   more_text="#141a24"     More button text color.
 *   more_bg="#ffffff"       More button background color.
 *   more_border="#d9dce2"   More button border color.
 *   more_border_width="1"   More button border width in px (0 = none).
 *   more_icon_color="#006bff" More button icon color.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function keb_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'inline'            => 'yes',
			'bg'                => '#ffffff',
			'width'             => 'full',
			'align'             => 'justify',
			'height'            => '56',
			'new_tab'           => 'no',
			'hover_bg'          => '#f0f1f3',
			'active_highlight'  => 'yes',
			'active_bg'         => '#eef2ff',
			'active_indicator'  => '#006bff',
			'border_color'      => '#e5e7eb',
			'border_width'      => '1',
			'visibility'        => 'no-mobile',
			'more_label'        => 'More',
			'more_icon'         => 'yes',
			'more_text'         => '#141a24',
			'more_bg'           => '#ffffff',
			'more_border'       => '#d9dce2',
			'more_border_width' => '1',
			'more_icon_color'   => '#006bff',
		),
		$atts,
		'kodezen_ecosystem_bar'
	);

	$truthy  = array( 'yes', 'true', '1' );
	$inline  = ! in_array( strtolower( (string) $atts['inline'] ), array( 'no', 'false', '0' ), true );
	$new_tab = in_array( strtolower( (string) $atts['new_tab'] ), $truthy, true );

	return keb_build_bar(
		array(
			'inline'          => $inline,
			'bg'              => $atts['bg'],
			'width'           => $atts['width'],
			'align'           => $atts['align'],
			'height'          => (int) $atts['height'],
			'new_tab'         => $new_tab,
			'hover_bg'         => $atts['hover_bg'],
			'active_highlight' => in_array( strtolower( (string) $atts['active_highlight'] ), $truthy, true ),
			'active_bg'        => $atts['active_bg'],
			'active_indicator' => $atts['active_indicator'],
			'border_color'    => $atts['border_color'],
			'border_width'    => (int) $atts['border_width'],
			'visibility'      => $atts['visibility'],
			'more_label'      => $atts['more_label'],
			'more_show_icon'  => in_array( strtolower( (string) $atts['more_icon'] ), $truthy, true ),
			'more_text'       => $atts['more_text'],
			'more_bg'         => $atts['more_bg'],
			'more_border'     => $atts['more_border'],
			'more_border_w'   => (int) $atts['more_border_width'],
			'more_icon_color' => $atts['more_icon_color'],
		)
	);
}
